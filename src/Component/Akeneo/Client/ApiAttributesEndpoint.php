<?php

namespace Misery\Component\Akeneo\Client;

use Misery\Component\Common\Client\ApiEndpointInterface;

class ApiAttributesEndpoint implements ApiEndpointInterface
{
    public const NAME = 'attributes';

    private const ALL = 'attributes';
    private const ONE = 'attributes/%code%';

    public function getAll(): string
    {
        return self::ALL;
    }

    public function getSingleEndPoint(): string
    {
        return self::ONE;
    }
}