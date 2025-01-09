<?php

namespace Misery\Component\Akeneo\Client;

use Misery\Component\Common\Client\ApiEndpointInterface;

class ApiOptionsEndpoint implements ApiEndpointInterface
{
    public const NAME = 'options';

    private const ALL = 'attributes/%s/options';
    private const ONE = 'attributes/%attribute%/options/%code%';

    public function getAll(): string
    {
        return self::ALL;
    }

    public function getSingleEndPoint(): string
    {
        return self::ONE;
    }
}