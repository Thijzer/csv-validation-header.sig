<?php

namespace Misery\Component\Connections\BusinessCentral\Client;

use Misery\Component\Common\Client\ApiClientAccountInterface;
use Misery\Component\Common\Client\ApiClientInterface;
use Misery\Component\Common\Client\ApiEndPointsInterface;
use Misery\Component\Common\Client\AuthenticatedAccount;
use Misery\Component\Common\Generator\UrlGenerator;
use Misery\Component\Common\Utils\ValueFormatter;
use Misery\Component\Connections\BusinessCentral\Client\EndPoints\MicrosoftDynamicsApiEndpoints;

class MicrosoftDynamicsOauthAccount implements ApiClientAccountInterface
{
    private const AUTH_URI = '/oauth2/v2.0/token';
    private const ROOT_URI = '/v2.0/%TenantID%/%environment%';

    private string $clientId;
    private string $secret;
    private string $scope;
    private string $tenantId;
    private string $environment;

    public function __construct(
        string $clientId,
        string $secret,
        string $tenantId,
        string $scope,
        string $environment
    ) {
        $this->clientId = $clientId;
        $this->secret = $secret;
        $this->scope = $scope;
        $this->tenantId = $tenantId;
        $this->environment = $environment;
    }

    public function authorize(ApiClientInterface $client): AuthenticatedAccount
    {
        $urlGenerator = new UrlGenerator(ValueFormatter::format(
            'https://login.microsoftonline.com/%TenantID%',
            ['TenantID' => $this->tenantId],
        ));

        $response = $client
            ->postXForm(
                $urlGenerator->generateFromDomain(self::AUTH_URI),
                [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->secret,
                    'scope' => $this->scope,
                    'redirect_uri' => 'https://businesscentral.dynamics.com/OAuthLanding.htm',
                ]
            )->getResponse();

        if ($response->getCode() === 422) {
            throw new \RuntimeException($response->getMessage());
        }

        $client->getUrlGenerator()->append(ValueFormatter::format(self::ROOT_URI, [
            'environment' => $this->environment,
            'TenantID' => $this->tenantId,
        ]));

        return new AuthenticatedAccount(
            $this,
            'microsoft_dynamics',
            $response->getContent('access_token'),
            null,
            $response->getContent('expires_in')
        );
    }

    public function refresh(ApiClientInterface $client, AuthenticatedAccount $account): AuthenticatedAccount
    {
    }

    public function getSupporterEndPoints(): ApiEndPointsInterface
    {
        return new MicrosoftDynamicsApiEndpoints();
    }
}