<?php

namespace Misery\Component\Connections\E5Dal\Client\Endpoints;

use Misery\Component\Common\Client\ApiEndpointInterface;

class E5DalApiDeltaArticleEndpoint implements ApiEndpointInterface
{
    public const NAME = 'delta_article';

    private const ALL = '/api/v3/article/changes';
    private const ONE = '/api/v3/article/%s';

    public function getAll(): string
    {
        return self::ALL;
    }

    public function getSingleEndPoint(): string
    {
        return self::ONE;
    }
}