<?php

namespace Misery\Component\Connections\E5Dal\Client\Endpoints;

use Misery\Component\Common\Client\ApiEndpointInterface;
use Misery\Component\Common\Client\ApiEndPointsInterface;

class E5DalApiEndpoints implements ApiEndPointsInterface
{
    private array $endpointSet = [
        E5DalApiModelEndpoint::NAME => E5DalApiModelEndpoint::class,
        E5DalApiArticleEndpoint::NAME => E5DalApiArticleEndpoint::class,
        E5DalApiSizeChartEndpoint::NAME => E5DalApiSizeChartEndpoint::class,
        E5DalApiSizeChartConversionEndpoint::NAME => E5DalApiSizeChartConversionEndpoint::class,
        E5DalApiProductsMediaEndpoint::NAME => E5DalApiProductsMediaEndpoint::class,

        E5DalApiDeltaModelEndpoint::NAME => E5DalApiDeltaModelEndpoint::class,
        E5DalApiDeltaArticleEndpoint::NAME => E5DalApiDeltaArticleEndpoint::class,
        E5DalApiDeltaSizeChartEndpoint::NAME => E5DalApiDeltaSizeChartEndpoint::class,
        E5DalApiDeltaSizeChartConversionEndpoint::NAME => E5DalApiDeltaSizeChartConversionEndpoint::class,
        E5DalApiDeltaProductsMediaEndpoint::NAME => E5DalApiDeltaProductsMediaEndpoint::class,
    ];

    public function getEndPoint(string $endpointName): ApiEndpointInterface
    {
        if (isset($this->endpointSet[$endpointName])) {
            return new $this->endpointSet[$endpointName];
        }
        throw new \Exception(sprintf('Unknown E5 Dal endpoint found %s', $endpointName));
    }
}