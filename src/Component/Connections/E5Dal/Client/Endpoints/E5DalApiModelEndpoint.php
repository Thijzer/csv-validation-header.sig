<?php

namespace Misery\Component\Connections\E5Dal\Client\Endpoints;

use Misery\Component\Common\Client\ApiEndpointInterface;

class E5DalApiModelEndpoint implements ApiEndpointInterface
{
    public const NAME = 'model';

    private const ALL = '/api/v3/model';
    private const ONE = '/api/v3/model/%s';

    public function getAll(): string
    {
        return self::ALL;
    }

    public function getSingleEndPoint(): string
    {
        return self::ONE;
    }
}