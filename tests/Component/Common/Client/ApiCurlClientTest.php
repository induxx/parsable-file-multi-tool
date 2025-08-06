<?php

declare(strict_types=1);

namespace Tests\Misery\Component\Common\Client;

use Misery\Component\Common\Client\ApiCurlClient;
use Misery\Component\Common\Client\ApiClientAccountInterface;
use Misery\Component\Common\Client\ApiEndPointsInterface;
use Misery\Component\Common\Client\Exception\PageNotFoundException;
use Misery\Component\Common\Client\Exception\UnauthorizedException;
use Misery\Component\Common\Client\AuthenticatedAccount;
use Misery\Component\Common\Client\ApiEndpointInterface;
use Misery\Component\Common\Client\ApiResponse;
use Misery\Component\Common\Generator\UrlGenerator;
use PHPUnit\Framework\TestCase;

class ApiCurlClientTest extends TestCase
{
    private ApiCurlClient $client;
    private \ReflectionClass $ref;

    protected function setUp(): void
    {
        // Create partial mock to stub rawRequest
        $this->client = $this->getMockBuilder(ApiCurlClient::class)
        ->setConstructorArgs(['https://api.example.com'])
        ->onlyMethods(['rawRequest'])
        ->getMock();

        // Prepare reflection on the ApiCurlClient class itself
        $this->ref = new \ReflectionClass(ApiCurlClient::class);

        // Replace UrlGenerator with a mock
        $urlGenProp = $this->ref->getProperty('urlGenerator');
        $urlGenProp->setAccessible(true);
        $urlGenMock = $this->createMock(UrlGenerator::class);
        $urlGenProp->setValue($this->client, $urlGenMock);
    }

    public function testAuthorizeSetsEndpointsAndAuthenticatedAccount(): void
    {
        $endpointsMock = $this->createMock(ApiEndPointsInterface::class);
        $authAccountMock = $this->createMock(AuthenticatedAccount::class);

        $accountMock = $this->createMock(ApiClientAccountInterface::class);
        $accountMock->expects(self::once())
        ->method('getSupporterEndPoints')
        ->willReturn($endpointsMock);
        $accountMock->expects(self::once())
        ->method('authorize')
        ->with($this->client)
            ->willReturn($authAccountMock);

        $this->client->authorize($accountMock);

        // Verify protected properties via reflection
        $endProp = $this->ref->getProperty('endpoints');
        $endProp->setAccessible(true);
        $this->assertSame($endpointsMock, $endProp->getValue($this->client));

        $authProp = $this->ref->getProperty('authenticatedAccount');
        $authProp->setAccessible(true);
        $this->assertSame($authAccountMock, $authProp->getValue($this->client));
    }

    public function testGetApiEndpointDelegatesToEndpoints(): void
    {
        $endpointMock = $this->createMock(ApiEndpointInterface::class);
        $endpointsMock = $this->createMock(ApiEndPointsInterface::class);
        $endpointsMock->expects(self::once())
        ->method('getEndPoint')
        ->with('foo')
        ->willReturn($endpointMock);

        // Inject endpoints
        $prop = $this->ref->getProperty('endpoints');
        $prop->setAccessible(true);
        $prop->setValue($this->client, $endpointsMock);

        $this->assertSame($endpointMock, $this->client->getApiEndpoint('foo'));
    }

    public function testRefreshTokenUpdatesAuthenticatedAccount(): void
    {
        // Create a mock for AuthenticatedAccount
        $oldAuthMock = $this->createMock(AuthenticatedAccount::class);

        // Create a mock for the underlying account interface and add a refresh method
        $accountInnerMock = $this->getMockBuilder(ApiClientAccountInterface::class)
            ->onlyMethods(['refresh'])
            ->getMockForAbstractClass();
        $newAuthMock = $this->createMock(AuthenticatedAccount::class);

        // Stub getAccount to return the interface mock
        $oldAuthMock->expects(self::once())
            ->method('getAccount')
            ->willReturn($accountInnerMock);

        // Stub refresh on the account interface mock
        $accountInnerMock->expects(self::once())
            ->method('refresh')
            ->with($this->client, $oldAuthMock)
            ->willReturn($newAuthMock);

        // Inject the initial authenticatedAccount
        $authProp = $this->ref->getProperty('authenticatedAccount');
        $authProp->setAccessible(true);
        $authProp->setValue($this->client, $oldAuthMock);

        // Perform token refresh
        $this->client->refreshToken();

        // Assert the authenticatedAccount was updated to the new instance
        $this->assertSame($newAuthMock, $authProp->getValue($this->client));
    }

    /**
     * @dataProvider requestMethodProvider
     */
    public function testHttpMethodsInvokeRawRequestCorrectly(
        string $method,
        string $driverMethod,
        array $data,
        array $expectedBodyAndHeaders
    ): void {
        $endpoint = 'https://api.example.com/resource';

        if ($driverMethod === 'search') {
            $urlGenProp = $this->ref->getProperty('urlGenerator');
            $urlGenProp->setAccessible(true);
            /** @var UrlGenerator|\PHPUnit\Framework\MockObject\MockObject $urlGen */
            $urlGen = $urlGenProp->getValue($this->client);
            $urlGen->expects(self::once())
                ->method('createParams')
                ->with($data[0])
                ->willReturn('?a=1');
            $url = $endpoint . '?a=1';
        } else {
            $url = $endpoint;
        }

        $this->client->expects(self::once())
            ->method('rawRequest')
            ->with(
                $method,
                $url,
                ...$expectedBodyAndHeaders
            )
            ->willReturn($this->createMock(ApiResponse::class));

        $result = $this->client->{$driverMethod}($endpoint, ...$data);
        $this->assertInstanceOf(ApiResponse::class, $result);
    }

    public function requestMethodProvider(): array
    {
        return [
            ['GET', 'get', [], [null, []]],
            ['DELETE', 'delete', [], [null, []]],
            ['GET', 'search', [['a' => 1]], [null, []]],
            ['POST', 'post', [['b' => 2], []], [json_encode(['b' => 2]), ['Content-Type' => 'application/json']]],
            ['POST', 'postXForm', [['c' => 3]], [http_build_query(['c' => 3]), ['Content-Type' => 'application/x-www-form-urlencoded']]],
            ['PATCH', 'patch', [['d' => 4]], [json_encode(['d' => 4]), ['Content-Type' => 'application/json']]],
            ['PATCH', 'multiPatch', [[[ ['id'=>5], ['id'=>6] ]]], [
                implode("\n", array_map('json_encode', [[['id'=>5], ['id'=>6]]])),
                ['Content-Type' => 'application/vnd.akeneo.collection+json']
            ]],
            ['GET', 'download', [], [null, []]],
        ];
    }

    public function testGetUrlGeneratorReturnsInstance(): void
    {
        $urlGenProp = $this->ref->getProperty('urlGenerator');
        $urlGenProp->setAccessible(true);
        $urlGen = $this->createMock(UrlGenerator::class);
        $urlGenProp->setValue($this->client, $urlGen);

        $this->assertSame($urlGen, $this->client->getUrlGenerator());
    }

    public function testClearResetsState(): void
    {
        $endpointsMock = $this->createMock(ApiEndPointsInterface::class);
        $propEndpoints = $this->ref->getProperty('endpoints');
        $propEndpoints->setAccessible(true);
        $propEndpoints->setValue($this->client, $endpointsMock);

        $this->client->clear();

        $this->assertNull($propEndpoints->getValue($this->client));

        $propUrlGen = $this->ref->getProperty('urlGenerator');
        $propUrlGen->setAccessible(true);
        $newGen = $propUrlGen->getValue($this->client);
        $this->assertInstanceOf(UrlGenerator::class, $newGen);
        $this->assertStringContainsString('no-domain', $newGen->getDomain());
    }

    public function testParseResponseThrows404(): void
    {
        $method = $this->ref->getMethod('parseResponse');
        $method->setAccessible(true);

        $this->expectException(PageNotFoundException::class);
        $method->invokeArgs($this->client, ['{"message":"Not Found"}', 404]);
    }

    public function testParseResponseThrows401(): void
    {
        $method = $this->ref->getMethod('parseResponse');
        $method->setAccessible(true);

        $this->expectException(UnauthorizedException::class);
        $method->invokeArgs($this->client, ['{"message":"Unauthorized"}', 401]);
    }

    public function testParseResponseMultiLineJsonCreatesMulti(): void
    {
        $method = $this->ref->getMethod('parseResponse');
        $method->setAccessible(true);

        $raw = json_encode(['a'=>1]) . "\n" . json_encode(['b'=>2]);
        $response = $method->invokeArgs($this->client, [$raw, 200]);
        $this->assertInstanceOf(ApiResponse::class, $response);
    }

    public function testParseResponseSingleJsonCreatesSingle(): void
    {
        $method = $this->ref->getMethod('parseResponse');
        $method->setAccessible(true);

        $raw = json_encode(['x'=>"y"]);
        $response = $method->invokeArgs($this->client, [$raw, 200]);
        $this->assertInstanceOf(ApiResponse::class, $response);
    }
}
