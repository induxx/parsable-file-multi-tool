<?php

declare(strict_types=1);

namespace Tests\Misery\Component\Common\Client;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Response;
use Misery\Component\Common\Client\ApiEndPointsInterface;
use Misery\Component\Common\Client\GuzzleApiClient;
use Misery\Component\Common\Client\ApiResponse;
use Misery\Component\Common\Client\ApiClientAccountInterface;
use Misery\Component\Common\Client\AuthenticatedAccount;
use Misery\Component\Common\Client\Exception\PageNotFoundException;
use Misery\Component\Common\Client\Exception\UnauthorizedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ApiGuzzleClientTest extends TestCase
{
    /** @var GuzzleClient&MockObject */
    private GuzzleClient $http;

    /** @var ApiEndPointsInterface&MockObject */
    private ApiEndPointsInterface $endpoints;

    /** @var ApiClientAccountInterface&MockObject */
    private ApiClientAccountInterface $apiAccount;

    /** @var AuthenticatedAccount&MockObject */
    private AuthenticatedAccount $authenticated;

    private GuzzleApiClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->http = $this->createMock(GuzzleClient::class);
        $this->endpoints = $this->createMock(ApiEndPointsInterface::class);
        $this->apiAccount = $this->createMock(ApiClientAccountInterface::class);
        $this->authenticated = $this->createMock(AuthenticatedAccount::class);

        // endpoints wiring used by authorize()
        $this->apiAccount
            ->method('getSupporterEndPoints')
            ->willReturn($this->endpoints);

        // authorize returns AuthenticatedAccount
        $this->apiAccount
            ->method('authorize')
            ->willReturn($this->authenticated);

        // authenticated account can inject auth headers
        $this->authenticated
            ->method('useToken')
            ->willReturnCallback(function (&$headers) {
                $headers['Authorization'] = 'Bearer test-token';
            });

        $this->client = new GuzzleApiClient('https://api.example.com');

        // Inject our mocked Guzzle client via reflection
        $ref = new ReflectionClass($this->client);
        $prop = $ref->getProperty('http');
        $prop->setAccessible(true);
        $prop->setValue($this->client, $this->http);

        // Finish auth flow so headers are applied in requests
        $this->client->authorize($this->apiAccount);
    }

    public function testGetReturnsSingleJsonResponse(): void
    {
        $this->http->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.example.com/items', $this->callback(function ($opts) {
                // Accept + Authorization are present
                return isset($opts['headers']['Accept'], $opts['headers']['Authorization']);
            }))
            ->willReturn(new Response(200, [], json_encode(['ok' => true])));

        $resp = $this->client->get('https://api.example.com/items');

        // assert ApiResponse (adapt if your ApiResponse differs)
        $this->assertInstanceOf(ApiResponse::class, $resp);
        $this->assertSame(200, $resp->getCode());
        $this->assertSame(['ok' => true], $resp->getContent());
    }

    public function testSearchAddsQueryParams(): void
    {
        $body = json_encode(['q' => 'widgets', 'page' => 2]);
        $this->http->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.example.com/search?q=widgets&page=2', $this->anything())
            ->willReturn(new Response(200, [], $body));

        $resp = $this->client->search('https://api.example.com/search', ['q' => 'widgets', 'page' => 2]);
        $this->assertSame(200, $resp->getCode());
        $this->assertSame(['q' => 'widgets', 'page' => 2], $resp->getContent());
    }

    public function testPostJson(): void
    {
        $this->http->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.example.com/items',
                $this->callback(function ($opts) {
                    $this->assertSame('application/json', $opts['headers']['Content-Type']);
                    $this->assertSame(json_encode(['a' => 1]), (string)$opts['body']);
                    return true;
                })
            )
            ->willReturn(new Response(201, [], json_encode(['id' => 123])));

        $resp = $this->client->post('https://api.example.com/items', ['a' => 1]);
        $this->assertSame(201, $resp->getCode());
        $this->assertSame(['id' => 123], $resp->getContent());
    }

    public function testPostXForm(): void
    {
        $this->http->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.example.com/login',
                $this->callback(function ($opts) {
                    $this->assertSame('application/x-www-form-urlencoded', $opts['headers']['Content-Type']);
                    parse_str((string)$opts['body'], $parsed);
                    $this->assertSame(['u' => 'me', 'p' => 'secret'], $parsed);
                    return true;
                })
            )
            ->willReturn(new Response(200, [], json_encode(['token' => 'abc'])));

        $resp = $this->client->postXForm('https://api.example.com/login', ['u' => 'me', 'p' => 'secret']);
        $this->assertSame(['token' => 'abc'], $resp->getContent());
    }

    public function testPatchJson(): void
    {
        $this->http->expects($this->once())
            ->method('request')
            ->with(
                'PATCH',
                'https://api.example.com/items/1',
                $this->callback(function ($opts) {
                    $this->assertSame('application/json', $opts['headers']['Content-Type']);
                    $this->assertSame(json_encode(['name' => 'X']), (string)$opts['body']);
                    return true;
                })
            )
            ->willReturn(new Response(200, [], json_encode(['id' => 1, 'name' => 'X'])));

        $resp = $this->client->patch('https://api.example.com/items/1', ['name' => 'X']);
        $this->assertSame(['id' => 1, 'name' => 'X'], $resp->getContent());
    }

    public function testMultiPatchNdjson(): void
    {
        $batch = [
            ['op' => 'replace', 'id' => 1],
            ['op' => 'replace', 'id' => 2],
        ];
        $expectedBody = json_encode($batch[0]) . "\n" . json_encode($batch[1]);

        $this->http->expects($this->once())
            ->method('request')
            ->with(
                'PATCH',
                'https://api.example.com/items/batch',
                $this->callback(function ($opts) use ($expectedBody) {
                    $this->assertSame('application/vnd.akeneo.collection+json', $opts['headers']['Content-Type']);
                    $this->assertSame($expectedBody, (string)$opts['body']);
                    return true;
                })
            )
            // Simulate NDJSON response (two lines)
            ->willReturn(new Response(200, [], "{\"ok\":1}\n{\"ok\":2}\n"));

        $resp = $this->client->multiPatch('https://api.example.com/items/batch', $batch);
        $this->assertInstanceOf(ApiResponse::class, $resp);
    }

    public function testDelete(): void
    {
        $this->http->expects($this->once())
            ->method('request')
            ->with('DELETE', 'https://api.example.com/items/1', $this->anything())
            ->willReturn(new Response(204, [], ''));

        $resp = $this->client->delete('https://api.example.com/items/1');
        $this->assertSame(204, $resp->getCode());
        $this->assertSame([], $resp->getContent());
    }

    public function testHandlesEmptyBody(): void
    {
        $this->http->expects($this->once())
            ->method('request')
            ->willReturn(new Response(200, [], ""));

        $resp = $this->client->get('https://api.example.com/empty');
        $this->assertSame(200, $resp->getCode());
        $this->assertSame([], $resp->getContent());
    }

    public function testThrowsUnauthorizedOn401(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->http->expects($this->once())
            ->method('request')
            ->willReturn(new Response(401, [], json_encode(['message' => 'nope'])));

        $this->client->get('https://api.example.com/secure');
    }

    public function testThrowsNotFoundOn404(): void
    {
        $this->expectException(PageNotFoundException::class);

        $this->http->expects($this->once())
            ->method('request')
            ->willReturn(new Response(404, [], json_encode(['message' => 'not here'])));

        $this->client->get('https://api.example.com/missing');
    }

    public function testNetworkErrorIsRewrappedAsRuntimeException(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->http->expects($this->once())
            ->method('request')
            ->willThrowException(new TransferException('network down'));

        $this->client->get('https://api.example.com/down');
    }

    public function testDownloadReturnsRawBody(): void
    {
        $payload = 'binary-image';

        $this->http->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.example.com/raw',
                $this->callback(function ($opts) {
                    $this->assertSame('application/json', $opts['headers']['Accept']);
                    return true;
                })
            )
            ->willReturn(new Response(200, [], $payload));

        $resp = $this->client->download('https://api.example.com/raw')->getContent();
        $this->assertSame($payload, $resp);
    }

    public function testDownloadReturnsNullForEmptyBody(): void
    {
        $this->http->expects($this->once())
            ->method('request')
            ->with('GET', 'https://api.example.com/raw', $this->anything())
            ->willReturn(new Response(200, [], ''));

        $resp = $this->client->download('https://api.example.com/raw')->getContent();
        $this->assertEmpty($resp);
    }
}
