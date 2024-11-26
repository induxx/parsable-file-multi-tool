<?php

namespace Misery\Component\Akeneo\Client;

use App\Component\Akeneo\Api\Resources\AkeneoAttributeOptionsResource;
use App\Component\Akeneo\Api\Resources\AkeneoAttributesResource;
use App\Component\Akeneo\Api\Resources\AkeneoCategoryResource;
use App\Component\Akeneo\Api\Resources\AkeneoFamiliesResource;
use App\Component\Akeneo\Api\Resources\AkeneoProductsResource;
use App\Component\Akeneo\Api\Resources\Filter\GlobalQueryFilterParameters;
use App\Component\Common\Cursor\CursorInterface;
use App\Component\Common\Cursor\MultiCursor;
use Assert\Assert;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Configurator\Configuration;
use Misery\Component\Reader\ReaderInterface;

class HttpReaderFactory implements RegisteredByNameInterface
{
    public function createFromConfiguration(array $configuration, Configuration $config): ReaderInterface
    {
        Assert::that(
            $configuration['type'],
            'type must be filled in.'
        )->notEmpty()->string()->inArray(['rest_api']);

        Assert::that(
            $configuration['endpoint'],
            'endpoint must be filled in.'
        )->notEmpty()->string();

        Assert::that(
            $configuration['method'],
            'method must be filled in.'
        )->notEmpty()->string()->inArray(['GET', 'get']);

        $endpoint = $configuration['endpoint'];

        $context = ['filters' => []];
        $context['container'] = $configuration['container'] ?? null;
        $configContext = $config->getContext();

        if (isset($configuration['identifier_filter_list'])) {
            $context['multiple'] = true;
            $context['list'] = is_array($configuration['identifier_filter_list']) ? $configuration['identifier_filter_list'] : $config->getList($configuration['identifier_filter_list']);
        }

        if (isset($configuration['filters'])) {
            $filters = $configuration['filters'];
            foreach ($filters as $fieldCode => $filterConfig) {
                foreach ($filterConfig as $filterType => $value) {
                    if ($filterType === 'list') {
                        $context['filters'][$fieldCode] = $config->getList($value);
                    }
                }
            }
        }

        $context['limiters'] = $configuration['limiters'] ?? [];
        if (isset($configuration['akeneo-filter'])) {
            $akeneoFilter = $configuration['akeneo-filter'];
            if (!isset($configContext['akeneo_filters'][$akeneoFilter])) {
                throw new \Exception(sprintf('The configuration is using an Akeneo filter code (%s) wich is not linked to this job profile.', $configuration['akeneo-filter']));
            }

            // create query string
            $context['limiters']['query_array'] = $configContext['akeneo_filters'][$akeneoFilter]['search'];
        }

        $accountCode = $configuration['account'] ?? null;
        if (!$accountCode) {
            throw new \Exception(sprintf('Account "%s" not found.', $accountCode));
        }
        $client = $config->getAccount($accountCode);
        if (!$client) {
            throw new \Exception(sprintf('Account "%s" not found.', $accountCode));
        }
        $filterParameters = new GlobalQueryFilterParameters();

        $queryString = $context['limiters']['querystring'] ?? null;

        if ($endpoint === 'attributes') {
            $resource = new AkeneoAttributesResource($client, $filterParameters);

            return new ApiReader($resource);
        } elseif ($endpoint === 'families') {
            $resource = new AkeneoFamiliesResource($client, $filterParameters);

            return new ApiReader($resource);
        } elseif ($endpoint === 'categories') {
            $resource = new AkeneoCategoryResource($client);

            if ($queryString) {
                $cursor = $resource->querystring($queryString);

                return new ApiReader($resource, $cursor);
            }
            return new ApiReader($resource);
        } elseif ($endpoint === 'products') {
            $resource = new AkeneoProductsResource($client, $filterParameters);

            if ($queryString) {
                $cursor = $resource->queryStringContent($queryString);

                return new ApiReader($resource, $cursor);
            }

            return new ApiReader($resource);
        } elseif ($endpoint === 'options') {
            $resource = new AkeneoAttributeOptionsResource($client);

            if (isset($configuration['identifier_filter_list'])) {
                $cursor = new MultiCursor();
                foreach ($configuration['identifier_filter_list'] as $identifier) {
                    $cursor->addCursor($resource->getAllByAttributeCode($identifier));
                }
                return new ApiReader($resource, $cursor);
            }

            return new ApiReader($resource);
        } else {
            throw new \Exception('Unknown endpoint: ' . $endpoint);
        }
    }

    public function getName(): string
    {
        return 'http_reader';
    }
}