<?php

namespace Misery\Component\Connections\E5Dal\Client;

use Misery\Component\Common\Client\ApiClientAccountInterface;
use Misery\Component\Common\Client\ApiClientInterface;
use Misery\Component\Common\Client\ApiEndPointsInterface;
use Misery\Component\Common\Client\AuthenticatedAccount;
use Misery\Component\Common\Client\PaginationCursor;
use Misery\Component\Connections\E5Dal\Client\Endpoints\E5DalApiEndpoints;

class E5DalAPIAccount implements ApiClientAccountInterface
{
    public function __construct(private string $token) {}

    public function getToken(): string
    {
        return $this->token;
    }

    public function authorize(ApiClientInterface $client): AuthenticatedAccount
    {
        $client->setHeaders([
            'Authorization' => $this->token,
        ]);

        return new AuthenticatedAccount($this, 'e5_dal', $this->token);
    }

    public function getSupporterEndPoints(): ApiEndPointsInterface
    {
        return new E5DalApiEndpoints();
    }

    public function getPaginator(ApiClientInterface $client, string $endpoint): PaginationCursor
    {
        return new E5PaginationCursor($client, $endpoint);
    }

    public function refresh(ApiClientInterface $client, AuthenticatedAccount $account): AuthenticatedAccount
    {
        dd('TODO refresh');
    }
}