<?php

namespace Misery\Component\Connections\BusinessCentral\Client\EndPoints;

use Misery\Component\Common\Client\ApiEndpointInterface;
use Misery\Component\Common\Client\ApiEndPointsInterface;
use Misery\Component\Common\Client\Endpoint\BasicApiEndpoint;

class MicrosoftDynamicsApiEndpoints implements ApiEndPointsInterface
{
    public const NAME = 'microsoft-dynamics';

    public function getEndPoint(string $endpointName): ApiEndpointInterface
    {
        return new BasicApiEndpoint($endpointName); # TODO: don't use basic api endpoint
    }
}