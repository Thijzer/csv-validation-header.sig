<?php

namespace Misery\Component\Common\Client\Endpoint;

use Misery\Component\Common\Client\ApiEndpointInterface;

class BasicApiEndpoint implements ApiEndpointInterface
{
    private string $endpoint;

    public function __construct(string $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public function getAll(): string
    {
        return $this->endpoint;
    }

    public function getSingleEndPoint(): string
    {
        // TODO
    }
}