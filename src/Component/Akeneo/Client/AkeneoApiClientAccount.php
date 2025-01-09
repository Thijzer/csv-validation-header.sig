<?php

namespace Misery\Component\Akeneo\Client;

use Misery\Component\Common\Client\ApiClientAccountInterface;
use Misery\Component\Common\Client\ApiClientInterface;
use Misery\Component\Common\Client\ApiEndPointsInterface;
use Misery\Component\Common\Client\AuthenticatedAccount;
use Misery\Component\Common\Client\PaginationCursor;

class AkeneoApiClientAccount implements ApiClientAccountInterface
{
    private const AUTH_URI = '/api/oauth/v1/token';
    public const ROOT_URI = '/api/rest/v1';

    /** @var string */
    private $username;
    private $password;
    private $clientId;
    private $secret;

    public function __construct(
        string $username,
        string $password,
        string $clientId,
        string $secret
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->clientId = $clientId;
        $this->secret = $secret;
    }

    public function authorize(ApiClientInterface $client): AuthenticatedAccount
    {
        $authorization = \base64_encode($this->clientId.':'.$this->secret);

        $client->setHeaders([
            'Authorization' => 'Basic '. $authorization,
        ]);

        $response = $client
            ->post(
                $client->getUrlGenerator()->generateFromDomain(self::AUTH_URI),
                [
                    'grant_type' => 'password',
                    'username' => $this->username,
                    'password' => $this->password,
                ]
            )->getResponse();

        if ($response->getCode() === 422) {
            throw new \RuntimeException($response->getMessage());
        }

        $client->getUrlGenerator()->append(self::ROOT_URI);

        return new AuthenticatedAccount(
            $this,
            $this->username,
            $response->getContent('access_token'),
            $response->getContent('refresh_token'),
            $response->getContent('expires_in')
        );
    }

    public function getSupporterEndPoints(): ApiEndPointsInterface
    {
        return new AkeneoApiEndpoints();
    }

    public function getPaginator(ApiClientInterface $client, string $endpoint): PaginationCursor
    {
        return new AkeneoPaginationCursor($client, $endpoint);
    }

    public function refresh(ApiClientInterface $client, AuthenticatedAccount $account): AuthenticatedAccount
    {
        $account->invalidate();
        $authorization = \base64_encode($this->clientId.':'.$this->secret);

        $client->setHeaders([
            'Authorization' => 'Basic '. $authorization,
        ]);

        $response = $client
            ->post(
                $client->getUrlGenerator()->generateFromDomain(self::AUTH_URI),
                [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $account->getRefreshToken(),
                ]
            )->getResponse();

        if ($response->getCode() === 422) {
            throw new \RuntimeException($response->getMessage());
        }

        return new AuthenticatedAccount(
            $this,
            $account->getUsername(),
            $response->getContent('access_token'),
            $response->getContent('refresh_token'),
            $response->getContent('expires_in')
        );
    }
}