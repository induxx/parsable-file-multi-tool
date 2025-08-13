<?php

namespace Misery\Component\Connections\E5Dal\Client\Endpoints;

use Misery\Component\Common\Client\ApiEndpointInterface;

class E5DalApiDeltaSizeChartEndpoint implements ApiEndpointInterface
{
    public const NAME = 'delta_sizeChart';

    private const ALL = '/api/v3/sizeChart/changes';
    private const ONE = '/api/v3/sizeChart/%s';

    public function getAll(): string
    {
        return self::ALL;
    }

    public function getSingleEndPoint(): string
    {
        return self::ONE;
    }
}