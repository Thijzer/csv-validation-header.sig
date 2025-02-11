<?php

namespace Misery\Component\Connections\E5Dal\Client\Endpoints;

use Misery\Component\Common\Client\ApiEndpointInterface;

class E5DalApiSizeChartConversionEndpoint implements ApiEndpointInterface
{
    public const NAME = 'sizeChartConversion';

    private const ALL = '/api/v3/sizeChartConversion';
    private const ONE = '/api/v3/sizeChartConversion/%s';

    public function getAll(): string
    {
        return self::ALL;
    }

    public function getSingleEndPoint(): string
    {
        return self::ONE;
    }
}