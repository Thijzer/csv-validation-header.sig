<?php

namespace Misery\Component\Connections\BusinessCentral\Client\EndPoints;

use Misery\Component\Common\Client\ApiEndpointInterface;
use Misery\Component\Common\Client\ApiEndPointsInterface;
use Misery\Component\Common\Client\Endpoint\BasicApiEndpoint;

class MicrosoftDynamicsApiEndpoints implements ApiEndPointsInterface
{
    public function getEndPoint(string $endpointName): ApiEndpointInterface
    {
        return new BasicApiEndpoint($endpointName);
    }
}