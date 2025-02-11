<?php

namespace Misery\Component\Common\Client\Endpoint;

use Misery\Component\Common\Client\ApiEndpointInterface;
use Misery\Component\Common\Client\ApiEndPointsInterface;

class BasicApiEndpoints implements ApiEndPointsInterface
{
    public function getEndPoint(string $endpointName): ApiEndpointInterface
    {
        return new BasicApiEndpoint($endpointName);
    }
}