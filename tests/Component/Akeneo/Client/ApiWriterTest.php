<?php

namespace Tests\Misery\Component\Akeneo\Client;

use App\Component\ChangeManager\ChangeManager;
use Misery\Component\Akeneo\Client\ApiWriter;
use Misery\Component\Common\Client\ApiClientInterface;
use Misery\Component\Common\Client\ApiEndpointInterface;
use Misery\Component\Common\Client\ApiResponse;
use Misery\Component\Common\Generator\UrlGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApiWriterTest extends TestCase
{
    private ApiClientInterface|MockObject $client;
    private ChangeManager|MockObject $changeManager;
    private ApiEndpointInterface $endpoint;

    protected function setUp(): void
    {
        if (!class_exists(ChangeManager::class)) {
            $this->markTestSkipped('ChangeManager class does not exist.');
        }

        $this->client = $this->createMock(ApiClientInterface::class);
        $this->changeManager = $this->createMock(ChangeManager::class);
        $this->endpoint = new class () implements ApiEndpointInterface {
            public const NAME = 'products';

            public function getAll(): string
            {
                return '/products';
            }

            public function getSingleEndPoint(): string
            {
                return '/products/%identifier%';
            }
        };
    }

    public function testPersistChangeIsCalledWithPersistFieldHint(): void
    {
        $urlGenerator = new UrlGenerator('https://example.test');
        $this->client->method('getUrlGenerator')->willReturn($urlGenerator);

        $data = [
            'identifier' => 'sku-1',
            'enabled' => true,
            '__change_manager_identifier' => 'sku-1',
            '__change_manager_persist_field' => '__change_manager_identifier',
        ];

        $expectedUrl = $urlGenerator->format('/products/%identifier%', $data);
        $expectedPayload = [
            'identifier' => 'sku-1',
            'enabled' => true,
        ];

        $this->client
            ->expects($this->once())
            ->method('patch')
            ->with($expectedUrl, $expectedPayload, [])
            ->willReturn(new ApiResponse(200, null, []));

        $this->changeManager
            ->expects($this->once())
            ->method('persistChange')
            ->with('sku-1');

        $writer = new ApiWriter(
            $this->client,
            $this->endpoint,
            'PATCH',
            null,
            $this->changeManager
        );

        $writer->write($data);
    }
}

