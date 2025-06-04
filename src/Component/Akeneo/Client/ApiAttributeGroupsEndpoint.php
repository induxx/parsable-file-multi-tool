<?php

namespace Misery\Component\Akeneo\Client;

use Misery\Component\Common\Client\ApiEndpointInterface;

class ApiAttributeGroupsEndpoint implements ApiEndpointInterface
{
    public const NAME = 'attribute-groups';

    private const ALL = 'attribute-groups';
    private const ONE = 'attribute-groups/%code%';

    public function getAll(): string
    {
        return self::ALL;
    }

    public function getSingleEndPoint(): string
    {
        return self::ONE;
    }
}