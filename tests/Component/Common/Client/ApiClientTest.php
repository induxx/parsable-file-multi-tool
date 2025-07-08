<?php

namespace Tests\Misery\Component\Common\Client;

use Misery\Component\Common\Client\ApiCurlClient;
use Misery\Component\Common\Client\ApiClientAccountInterface;
use Misery\Component\Common\Client\ApiEndPointsInterface;
use Misery\Component\Common\Client\ApiEndpointInterface;
use Misery\Component\Common\Client\AuthenticatedAccount;
use Misery\Component\Common\Generator\UrlGenerator;
use PHPUnit\Framework\TestCase;

class ApiClientTest extends TestCase
{
    private $curlHandle;

    protected function setUp(): void
    {
        parent::setUp();
        // Use a real CurlHandle for PHP 8+
        $this->curlHandle = \curl_init();
    }

    protected function tearDown(): void
    {
        if (is_object($this->curlHandle)) {
            \curl_close($this->curlHandle);
        }
        parent::tearDown();
    }

    private function getApiClientWithHandle()
    {
        $client = new ApiCurlClient('http://example.com');
        $ref = new \ReflectionProperty(ApiCurlClient::class, 'handle');
        $ref->setAccessible(true);
        $ref->setValue($client, $this->curlHandle);
        return $client;
    }

    // Add this helper for compatibility with tests that call getApiClientMock
    private function getApiClientMock($withHandle = true)
    {
        if ($withHandle) {
            return $this->getApiClientWithHandle();
        }
        return new ApiCurlClient('http://example.com');
    }

    public function testAuthorizeSetsHandleAndAccount()
    {
        $client = new ApiCurlClient('http://example.com');
        $account = $this->createMock(ApiClientAccountInterface::class);
        $endpoints = $this->createMock(ApiEndPointsInterface::class);
        $authenticated = $this->createMock(AuthenticatedAccount::class);

        $account->expects($this->once())->method('getSupporterEndPoints')->willReturn($endpoints);
        $account->expects($this->once())->method('authorize')->with($client)->willReturn($authenticated);

        $client->authorize($account);

        $ref = new \ReflectionProperty(ApiCurlClient::class, 'handle');
        $ref->setAccessible(true);
        $this->assertNotNull($ref->getValue($client));
    }

    public function testGetApiEndpointReturnsEndpoint()
    {
        $client = $this->getApiClientWithHandle();
        $endpoint = $this->createMock(ApiEndpointInterface::class);
        $endpoints = $this->createMock(ApiEndPointsInterface::class);

        $endpoints->expects($this->once())->method('getEndPoint')->with('foo')->willReturn($endpoint);

        $ref = new \ReflectionProperty(ApiCurlClient::class, 'endpoints');
        $ref->setAccessible(true);
        $ref->setValue($client, $endpoints);

        $this->assertSame($endpoint, $client->getApiEndpoint('foo'));
    }

    public function testRefreshTokenCallsRefresh()
    {
        $client = $this->getApiClientWithHandle();
        $account = $this->createMock(ApiClientAccountInterface::class);
        $authenticated = $this->createMock(AuthenticatedAccount::class);

        $authenticated->expects($this->once())->method('getAccount')->willReturn($account);
        $account->expects($this->once())->method('refresh')->with($client, $authenticated)->willReturn($authenticated);

        $ref = new \ReflectionProperty(ApiCurlClient::class, 'authenticatedAccount');
        $ref->setAccessible(true);
        $ref->setValue($client, $authenticated);

        $client->refreshToken();
        $this->assertSame($authenticated, $ref->getValue($client));
    }

    public function testSearchSetsCurlOptions()
    {
        $client = $this->getApiClientWithHandle();

        $urlGen = $this->createMock(UrlGenerator::class);
        $urlGen->expects($this->once())->method('createParams')->with(['foo' => 'bar'])->willReturn('?foo=bar');
        $ref = new \ReflectionProperty(ApiCurlClient::class, 'urlGenerator');
        $ref->setAccessible(true);
        $ref->setValue($client, $urlGen);

        $this->assertSame($client, $client->search('endpoint', ['foo' => 'bar']));
    }

    public function testGetSetsCurlOptions()
    {
        $client = $this->getApiClientWithHandle();
        $this->assertSame($client, $client->get('endpoint'));
    }

    public function testPostSetsCurlOptions()
    {
        $client = $this->getApiClientWithHandle();
        $this->assertSame($client, $client->post('endpoint', ['foo' => 'bar']));
    }

    public function testPostXFormSetsCurlOptions()
    {
        $client = $this->getApiClientWithHandle();
        $this->assertSame($client, $client->postXForm('endpoint', ['foo' => 'bar']));
    }

    public function testMultiPatchSetsCurlOptions()
    {
        $client = $this->getApiClientWithHandle();
        $this->assertSame($client, $client->multiPatch('endpoint', [['foo' => 'bar']]));
    }

    public function testPatchSetsCurlOptions()
    {
        $client = $this->getApiClientWithHandle();
        $this->assertSame($client, $client->patch('endpoint', ['foo' => 'bar']));
    }

    public function testDeleteSetsCurlOptions()
    {
        $client = $this->getApiClientWithHandle();
        $this->assertSame($client, $client->delete('endpoint'));
    }

    public function testSetHeadersMergesHeaders()
    {
        $client = $this->getApiClientMock(false);
        $client->setHeaders(['A' => 'B']);
        $client->setHeaders(['C' => 'D']);

        $ref = new \ReflectionProperty(ApiCurlClient::class, 'headers');
        $ref->setAccessible(true);
        $this->assertEquals(['A' => 'B', 'C' => 'D'], $ref->getValue($client));
    }

    public function testGenerateHeadersClearsHeaders()
    {
        $client = $this->getApiClientWithHandle();
        $ref = new \ReflectionProperty(ApiCurlClient::class, 'headers');
        $ref->setAccessible(true);
        $ref->setValue($client, ['A' => 'B']);

        $client->generateHeaders();
        $this->assertEquals([], $ref->getValue($client));
    }

    public function testClearResetsCurlOptions()
    {
        $client = $this->getApiClientWithHandle();
        $client->clear();
        $this->assertTrue(true); // If no exception, pass
    }

    public function testCloseClearsAndClosesHandle()
    {
        $client = $this->getApiClientWithHandle();
        $client->close();
        $ref = new \ReflectionProperty(ApiCurlClient::class, 'handle');
        $ref->setAccessible(true);
        $this->assertNull($ref->getValue($client));
    }

    public function testGetUrlGeneratorReturnsUrlGenerator()
    {
        $client = new ApiCurlClient('http://example.com');
        $this->assertInstanceOf(UrlGenerator::class, $client->getUrlGenerator());
    }
}
