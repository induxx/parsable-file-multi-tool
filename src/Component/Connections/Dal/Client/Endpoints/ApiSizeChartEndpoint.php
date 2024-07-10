<?php

namespace Misery\Component\Connections\Dal\Client\Endpoints;

use Misery\Component\Common\Client\ApiEndpointInterface;

class ApiSizeChartEndpoint implements ApiEndpointInterface
{
    public const NAME = 'sizeChart';

    private const ALL = '/api/v3/sizeChart';
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