<?php

namespace Misery\Component\Common\Client;

use Misery\Component\Common\Generator\UrlGenerator;
use Misery\Component\Common\Client\Exception\PageNotFoundException;
use Misery\Component\Common\Client\Exception\UnauthorizedException;

class ApiCurlClient implements ApiClientInterface
{
    private UrlGenerator $urlGenerator;
    private ?ApiEndPointsInterface $endpoints = null;
    private AuthenticatedAccount $authenticatedAccount;

    public function __construct(string $domain)
    {
        $this->urlGenerator = new UrlGenerator($domain);
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
            if ($headers['Content-Type'] === 'application/json') {
                $body = json_encode($data);
            } else {
                $body = http_build_query($data);
            }
        }
        return $this->rawRequest($method, $url, $body, $headers);
    }

    protected function rawRequest(string $method, string $url, ?string $body, array $headers = []): ApiResponse
    {
        $ch = curl_init();
        $headers = $this->buildHeaders($headers);
        curl_setopt_array($ch, [
            CURLOPT_URL             => $url,
            CURLOPT_CUSTOMREQUEST   => $method,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_2TLS, // HTTP/2 over TLS with HTTP/1.1 fallback
            CURLOPT_SSL_VERIFYPEER  => true,
            CURLOPT_SSL_VERIFYHOST  => 2,
            CURLOPT_ENCODING        => '',    // auto-decode gzip/deflate
        ]);

        \curl_setopt($ch, CURLOPT_HTTPHEADER, array_map(function ($key, $value) {
            return $key . ': ' . $value;
        }, array_keys($headers), $headers));

        if (null !== $body) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        $error    = curl_error($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno) {
            throw new \RuntimeException($error, $errno);
        }

        return $this->parseResponse($response, $status);
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

    private function parseResponse(string $raw, int $status): ApiResponse
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
            return ApiResponse::create($multi[0], $status);
        }

        return ApiResponse::create([], $status);
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

//        $this->setAuthenticationHeaders();
//        $this->setHeaders(['Content-Type' => 'application/json']);
//
//        \curl_setopt($this->handle, CURLOPT_URL, $endpoint);
//        \curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, "GET");
//        \curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
//        \curl_setopt($this->handle, CURLOPT_HEADER, false);
//
//        $this->generateHeaders();
//
//        $response = \curl_exec($this->handle);
//        $status = \curl_getinfo($this->handle, CURLINFO_HTTP_CODE);
//
//        if ($response === false) {
//            throw new \RuntimeException(\curl_error($this->handle), \curl_errno($this->handle));
//        }
//
//        if ($status >= 200 && $status < 300) {
//            return $response !== '' ? $response : null;
//        }
//
//        throw new \RuntimeException('Download failed: HTTP ' . $status . ' - ' . $response, $status);
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
