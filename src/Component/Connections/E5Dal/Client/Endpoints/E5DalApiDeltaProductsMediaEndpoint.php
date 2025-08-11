<?php

namespace Misery\Component\Connections\E5Dal\Client\Endpoints;

use Misery\Component\Common\Client\ApiEndpointInterface;

class E5DalApiDeltaProductsMediaEndpoint implements ApiEndpointInterface
{
    public const NAME = 'delta_products_media';

    private const ALL = '/api/v2/products/media/changes';
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