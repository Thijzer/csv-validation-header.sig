<?php

namespace Misery\Component\Akeneo\Client;

use Misery\Component\Common\Client\ApiEndpointInterface;

class ApiAssetsEndpoint implements ApiEndpointInterface
{
    public const NAME = 'assets';

    private const ALL = 'asset-families/%s/assets';
    private const ONE = 'asset-families/%asset_family_code%/assets/%code%';

    public function getAll(): string
    {
        return self::ALL;
    }

    public function getSingleEndPoint(): string
    {
        return self::ONE;
    }
}