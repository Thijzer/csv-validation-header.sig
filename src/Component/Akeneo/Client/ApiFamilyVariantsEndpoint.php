<?php

namespace Misery\Component\Akeneo\Client;

use Misery\Component\Common\Client\ApiEndpointInterface;

class ApiFamilyVariantsEndpoint implements ApiEndpointInterface
{
    public const NAME = 'family_variants';

    private const ALL = 'families/%s/variants';
    private const ONE = 'families/%family%/variants/%code%';

    public function getAll(): string
    {
        return self::ALL;
    }

    public function getSingleEndPoint(): string
    {
        return self::ONE;
    }
}