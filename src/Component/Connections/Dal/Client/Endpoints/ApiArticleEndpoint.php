<?php

namespace Misery\Component\Connections\Dal\Client\Endpoints;

use Misery\Component\Common\Client\ApiEndpointInterface;

class ApiArticleEndpoint implements ApiEndpointInterface
{
    public const NAME = 'article';

    private const ALL = '/api/v3/article';
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