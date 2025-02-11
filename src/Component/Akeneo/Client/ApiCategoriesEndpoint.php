<?php

namespace Misery\Component\Akeneo\Client;

use Misery\Component\Common\Client\ApiEndpointInterface;

class ApiCategoriesEndpoint implements ApiEndpointInterface
{
    public const NAME = 'categories';
    private const ALL = 'categories';
    private const ONE = 'categories/%code%';

    public function getAll(): string
    {
        return self::ALL;
    }

    public function getSingleEndPoint(): string
    {
        return self::ONE;
    }
}