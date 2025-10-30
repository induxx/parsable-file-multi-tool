<?php

namespace Misery\Component\Common\Client;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Misery\Component\Common\Generator\UrlGenerator;
use Psr\Http\Message\ResponseInterface;
use Misery\Component\Common\Client\Exception\PageNotFoundException;
use Misery\Component\Common\Client\Exception\UnauthorizedException;

class GuzzleApiClient implements ApiClientInterface
{
    private UrlGenerator $urlGenerator;
    private ?ApiEndPointsInterface $endpoints = null;
    private AuthenticatedAccount $authenticatedAccount;
    private GuzzleClient $http;

    public function __construct(string $domain)
    {
        $this->urlGenerator = new UrlGenerator($domain);

        // Build a handler stack with a conservative retry for HTTP/2/TLS flakiness.
        $stack = HandlerStack::create();
        $stack->push(Middleware::retry(
            function ($retries, $request, $response = null, $exception = null) {
                if ($retries >= 2) {
                    return false;
                }
                // Retry on network-level hiccups or specific HTTP/2/TLS messages
                if ($exception instanceof TransferException) {
                    $msg = $exception->getMessage();
                    if (stripos($msg, 'PROTOCOL_ERROR') !== false) return true;
                    if (stripos($msg, 'unexpected eof') !== false) return true;
                    if (stripos($msg, 'SSL_read') !== false) return true;
                    if (stripos($msg, 'stream was not closed cleanly') !== false) return true;
                }
                // Retry HTTP/2 GOAWAY-ish or gateway-y 502/503/504
                if ($response instanceof ResponseInterface) {
                    $code = $response->getStatusCode();
                    if (in_array($code, [502, 503, 504], true)) return true;
                }
                return false;
            },
            function ($retries) {
                // simple exponential backoff: 100ms, 200ms
                return 100 * (2 ** ($retries - 1));
            }
        ));

        $this->http = new GuzzleClient([
            'handler'        => $stack,
            'http_errors'    => false,   // we raise our own exceptions consistently
            'allow_redirects'=> ['max' => 10],
            'decode_content' => true,    // gzip/deflate/br
            'version'        => 2.0,     // prefer HTTP/2; Guzzle/cURL will fall back when needed
            'verify'         => true,    // verify TLS certs
            // Force TLS 1.2 if you need to (helps in some OpenSSL 3 + server combos)
            'curl'           => [
                \CURLOPT_SSL_VERIFYPEER => true,
                \CURLOPT_SSL_VERIFYHOST => 2,
                \CURLOPT_SSLVERSION     => \CURL_SSLVERSION_TLSv1_2, // keep if you must force TLS 1.2
                // Robustness knobs (optional):
                \CURLOPT_HTTP_VERSION   => \CURL_HTTP_VERSION_2TLS,
            ],
            'timeout'        => 30,
            //'connect_timeout'=> 10,
            // default connect_timeout is 0 (wait indefinitely), which is usually fine with retries, but it creates long waits on network issues
            // issues: dossche-acc gave this Guzzle error: Connection timed out after 10000 milliseconds ""
            // todo: re-implement connect_timeout if needed for retry behavior
        ]);
    }

    public function authorize(ApiClientAccountInterface $account): void
    {
        $this->endpoints = $account->getSupporterEndPoints();
        $this->authenticatedAccount = $account->authorize($this);
    }

    public function getApiEndpoint(string $apiEndpoint): ApiEndpointInterface
    {
        return $this->endpoints->getEndPoint($apiEndpoint);
    }

    public function refreshToken(): void
    {
        $acct      = $this->authenticatedAccount->getAccount();
        $oldAuth   = $this->authenticatedAccount;
        $this->authenticatedAccount = $acct->refresh($this, $oldAuth);
    }

    public function search(string $endpoint, array $params = []): ApiResponse
    {
        $url = $endpoint . $this->urlGenerator->createParams($params);
        return $this->request('GET', $url);
    }

    public function get(string $endpoint): ApiResponse
    {
        return $this->request('GET', $endpoint);
    }

    public function post(string $endpoint, array $postData, array $headers = []): ApiResponse
    {
        $headers['Content-Type'] = 'application/json';
        return $this->request('POST', $endpoint, $postData, $headers);
    }

    public function postXForm(string $endpoint, array $data): ApiResponse
    {
        $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        return $this->request('POST', $endpoint, $data, $headers);
    }

    public function patch(string $endpoint, array $patchData): ApiResponse
    {
        $headers['Content-Type'] = 'application/json';
        return $this->request('PATCH', $endpoint, $patchData, $headers);
    }

    public function multiPatch(string $endpoint, array $dataSet): ApiResponse
    {
        $body = implode("\n", array_map('json_encode', $dataSet));
        $headers['Content-Type'] = 'application/vnd.akeneo.collection+json';
        return $this->rawRequest('PATCH', $endpoint, $body, $headers);
    }

    public function delete(string $endpoint): ApiResponse
    {
        return $this->request('DELETE', $endpoint);
    }

    private function request(string $method, string $url, array $data = null, array $headers = []): ApiResponse
    {
        $body = null;
        if ($data !== null) {
            if (($headers['Content-Type'] ?? '') === 'application/json') {
                $body = json_encode($data);
            } else {
                $body = http_build_query($data);
            }
        }
        return $this->rawRequest($method, $url, $body, $headers);
    }

    protected function rawRequest(string $method, string $url, ?string $body, array $headers = []): ApiResponse
    {
        $headers = $this->buildHeaders($headers);

        try {
            $response = $this->http->request($method, $url, [
                'headers' => $headers,
                'body'    => $body,
            ]);
        } catch (TransferException $e) {
            // mimic previous behavior: \RuntimeException with message and code when cURL/Guzzle fails
            throw new \RuntimeException($e->getMessage(), (int)$e->getCode(), $e);
        }

        $status   = $response->getStatusCode();
        $raw      = (string)$response->getBody();
        $headers  = $response->getHeaders();

        return $this->parseResponse($raw, $status, $headers);
    }

    private function buildHeaders(array $h = []): array
    {
        $h['Accept'] = 'application/json';

        // let the authenticatedAccount inject its auth header
        if (isset($this->authenticatedAccount)) {
            $this->authenticatedAccount->useToken($h);
        }

        return $h;
    }

    private function parseResponse(string $raw, int $status, array $headers = []): ApiResponse
    {
        if ($status === 404) {
            $body = @json_decode($raw, true) ?: [];
            throw new PageNotFoundException($body['message'] ?? 'Page Not Found 404');
        }
        if ($status === 401) {
            $body = @json_decode($raw, true) ?: [];
            throw new UnauthorizedException($body['message'] ?? 'Unauthorized');
        }

        $lines = array_filter(explode("\n", $raw), fn($l) => trim($l) !== '');
        $multi = array_map(fn($l) => json_decode($l, true), $lines);

        if (count($multi) > 1) {
            return ApiResponse::createFromMulti($multi);
        }
        if (count($multi) === 1) {
            return ApiResponse::create($multi[0], $status, $headers);
        }

        return ApiResponse::create([], $status, $headers);
    }

    public function log(string $message, int $statusCode = null, $content): void
    {
        $message = sprintf("[%s] %s %s %s",
            date('Y-m-d H:i:s'),
            $message,
            $statusCode,
            json_encode($content)
        );

        file_put_contents(
            '/app/var/logs/curl.log',
            PHP_EOL . $message,
            FILE_APPEND
        );
    }

    public function getUrlGenerator(): UrlGenerator
    {
        return $this->urlGenerator;
    }

    public function getPaginator(string $startUrl): PaginationCursor
    {
        return $this->authenticatedAccount->getAccount()->getPaginator($this, $startUrl);
    }

    /**
     * Download the raw response body from the endpoint.
     * Returns string|null on success, throws on error.
     */
    public function download(string $endpoint): ApiResponse
    {
        return $this->request('GET', $endpoint);
    }

    public function close(): void
    {
        $this->clear();
    }

    public function clear(): void
    {
        // Reset any internal state if needed
        $this->endpoints = null;
        $this->urlGenerator = new UrlGenerator('no-domain');
    }
}
