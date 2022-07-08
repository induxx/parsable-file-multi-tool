<?php

namespace Misery\Component\Akeneo\Client;

use Assert\Assert;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Configurator\Configuration;
use Misery\Component\Writer\ItemWriterInterface;

class HttpWriterFactory implements RegisteredByNameInterface
{
    public function createFromConfiguration(array $configuration, Configuration $config): ItemWriterInterface
    {
        Assert::that(
            $configuration['type'],
            'type must be filled in.'
        )->notEmpty()->string()->inArray(['rest_api']);

        Assert::that(
            $configuration['account'],
            'account must be filled in.'
        )->notEmpty()->string();

        if ($configuration['type'] === 'rest_api') {

            Assert::that(
                $configuration['endpoint'],
                'endpoint must be filled in.'
            )->notEmpty()->string();

            Assert::that(
                $configuration['method'],
                'method must be filled in.'
            )->notEmpty()->string()->inArray([
                'GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'MULTI_PATCH', 'get', 'post', 'put', 'delete', 'patch', 'multi_patch',
            ]);

            $endpoint = $configuration['endpoint'];
            $method = $configuration['method'];

            $endpointSet = [
                ApiAttributesEndpoint::NAME => ApiAttributesEndpoint::class,
                ApiProductsEndpoint::NAME => ApiProductsEndpoint::class,
            ];

            $endpoint = $endpointSet[$endpoint] ?? null;
            Assert::that(
                $endpoint,
                'endpoint must be valid.'
            )->notNull();

            return new ApiWriter(
                $config->getAccount($configuration['account']),
                new $endpoint,
                $method
            );
        }

        throw new \Exception('Unknown type: ' . $configuration['type']);
    }

    public function getName(): string
    {
        return 'http_writer';
    }
}