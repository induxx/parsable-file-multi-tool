<?php

namespace Misery\Component\Connections\E5Dal\Client\Endpoints;

use Misery\Component\Common\Client\ApiEndpointInterface;

class E5DalApiDeltaSizeChartConversionEndpoint implements ApiEndpointInterface
{
    public const NAME = 'delta_sizeChartConversion';

    private const ALL = '/api/v3/sizeChartConversion/changes';
    private const ONE = '/api/v3/sizeChartConversion/%s';

    public function getAll(): string
    {
        return self::ALL;
    }

    public function getSingleEndPoint(): string
    {
        return self::ONE;
    }
}