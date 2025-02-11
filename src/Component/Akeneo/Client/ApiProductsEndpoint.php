<?php

namespace Misery\Component\Akeneo\Client;

use Misery\Component\Common\Client\ApiEndpointInterface;

class ApiProductsEndpoint implements ApiEndpointInterface
{
    public const NAME = 'products';
    private const ALL = 'products';
    private const ONE = 'products/%s';

    public function getAll(): string
    {
        return self::ALL;
    }

    public function getSingleEndPoint(): string
    {
        return self::ONE;
    }
}