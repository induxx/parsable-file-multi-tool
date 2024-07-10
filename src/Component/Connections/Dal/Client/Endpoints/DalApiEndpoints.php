<?php

namespace Misery\Component\Connections\Dal\Client\Endpoints;

use Misery\Component\Common\Client\ApiEndpointInterface;
use Misery\Component\Common\Client\ApiEndPointsInterface;

class DalApiEndpoints implements ApiEndPointsInterface
{
    private array $endpointSet = [
        ApiModelEndpoint::NAME => ApiModelEndpoint::class,
        ApiArticleEndpoint::NAME => ApiArticleEndpoint::class,
        ApiSizeChartEndpoint::NAME => ApiSizeChartEndpoint::class,
        ApiSizeChartConversionEndpoint::NAME => ApiSizeChartEndpoint::class,
        ApiProductsMediaEndpoint::NAME => ApiProductsMediaEndpoint::class,
    ];

    public function getEndPoint(string $endpointName): ApiEndpointInterface
    {
        if (isset($this->endpointSet[$endpointName])) {
            return new $this->endpointSet[$endpointName];
        }
        throw new \Exception(sprintf('Unknown Dal E5 endpoint found %s', $endpointName));
    }
}