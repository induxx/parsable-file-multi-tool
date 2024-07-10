<?php

namespace Misery\Component\Connections\Dal\Client\Endpoints;

use Misery\Component\Common\Client\ApiEndpointInterface;

class ApiProductsMediaEndpoint implements ApiEndpointInterface
{
    public const NAME = 'products_media';

    private const ALL = '/api/v2/products/media';
    private const ONE = '/api/v2/products/media/%s';

    public function getAll(): string
    {
        return self::ALL;
    }

    public function getSingleEndPoint(): string
    {
        return self::ONE;
    }
}