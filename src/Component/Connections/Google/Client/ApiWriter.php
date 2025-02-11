<?php

namespace Misery\Component\Connections\Google\Client;

use Misery\Component\Common\Client\ApiClientInterface;
use Misery\Component\Common\Client\ApiEndpointInterface;
use Misery\Component\Writer\ItemWriterInterface;

class ApiWriter implements ItemWriterInterface
{
    private $client;
    private $endpoint;

    public function __construct(ApiClientInterface $client, ApiEndpointInterface $endpoint)
    {
        $this->client = $client;
        $this->endpoint = $endpoint;
    }

    private function post(array $data): void
    {
        $this->client
            ->post($this->client->getUrlGenerator()->generate($this->endpoint->getSingleEndPoint()), $data)
            ->getResponse()
        ;
    }

    public function write(array $data): void
    {
        $this->post($data);
    }

    public function close(): void
    {
        // TODO: Implement close() method.
    }
}
