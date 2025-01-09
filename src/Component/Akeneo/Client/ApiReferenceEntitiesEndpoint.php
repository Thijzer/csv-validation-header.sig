<?php

namespace Misery\Component\Akeneo\Client;

use Misery\Component\Common\Client\ApiEndpointInterface;

class ApiReferenceEntitiesEndpoint implements ApiEndpointInterface
{
    public const NAME = 'reference-entities';

    private const ALL = 'reference-entities/%s/records';
    private const ONE = 'reference-entities/%reference-code%/records/%code%';

    public function getAll(): string
    {
        return self::ALL;
    }

    public function getSingleEndPoint(): string
    {
        return self::ONE;
    }
}