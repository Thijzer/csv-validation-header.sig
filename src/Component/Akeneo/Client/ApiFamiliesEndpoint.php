<?php

namespace Misery\Component\Akeneo\Client;

use Misery\Component\Common\Client\ApiEndpointInterface;

class ApiFamiliesEndpoint implements ApiEndpointInterface
{
    public const NAME = 'families';
    private const ALL = 'families';
    private const ONE = 'families/%code%';

    public function getAll(): string
    {
        return self::ALL;
    }

    public function getSingleEndPoint(): string
    {
        return self::ONE;
    }
}