<?php

namespace Misery\Component\Connections\Google\Client;

use Assert\Assert;
use \Firebase\JWT\JWT;
use Misery\Component\Common\Client\ApiClientAccountInterface;
use Misery\Component\Common\Client\ApiClientInterface;
use Misery\Component\Common\Client\AuthenticatedAccount;

class GoogleApiClientServiceAccount implements ApiClientAccountInterface
{
    private const ALGORITHM = 'HS256';
    private const AUTH_URI = 'https://accounts.google.com/o/oauth2/auth';
    private const TOKEN_URI = 'https://oauth2.googleapis.com/token';
    public const ROOT_URI = '/api/rest/v1/%s';
    private const DEFAULT_EXPIRY_SECONDS = 3600; // 1 hour
    private const DEFAULT_SKEW_SECONDS = 60; // 1 minute


    /** @var string */
    private $clientId;
    private $redirectUri;
    private $clientEmail;
    private $privateKey;
    private $privateKeyId;
    private $quotaProject;

    public function __construct(
        string $clientId,
        string $clientEmail,
        string $privateKey,
        string $privateKeyId,
        string $quotaProject
    ) {
        $this->clientId = $clientId;
        $this->clientEmail = $clientEmail;
        $this->privateKey = $privateKey;
        $this->privateKeyId = $privateKeyId;
        $this->quotaProject = $quotaProject;
    }

    public function authorize(ApiClientInterface $client): AuthenticatedAccount
    {
        Assert::that($this->redirectUri, 'A valid Redirect URI needs to be set.')->notEmpty()->url();
        # scope https://www.googleapis.com/auth/spreadsheets
        # https://github.com/induxx/akeneo-datamodel-importer/blob/master/src/Client/GoogleClient.php
        # https://github.com/googleapis/google-api-php-client-services/blob/master/src/Google/Service/Sheets.php

        // JWT Payload and token
        $now = \time();

        $payload = array(
            "iss" => $this->clientEmail,
            "aud" => self::TOKEN_URI,
            "iat" => $now + self::DEFAULT_EXPIRY_SECONDS,
            "nbf" => $now + self::DEFAULT_SKEW_SECONDS,
        );

        $jwtToken = JWT::encode(
            $payload,
            $this->privateKey,
            self::ALGORITHM,
            $this->privateKeyId
        );

        // make credentials and fetch the refresh token

        $client->setHeaders([
            'Cache-Control' => 'no-store',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        # https://www.ibm.com/docs/en/was-liberty/base?topic=uocpao2as-json-web-token-jwt-oauth-client-authorization-grants
        $response = $client
            ->post(
                self::AUTH_URI,
                [
                    'grant_type' => 'service_account',
                    'assertion' => $jwtToken,
                    'client_id' => $this->clientId,
                    #'secret' => '',
                    #'scope' => '',
                ]
            )->getResponse();

        if ($response->getCode() === 422) {
            throw new \RuntimeException($response->getMessage());
        }

        $client->getUrlGenerator()->append(self::ROOT_URI);

        return new AuthenticatedAccount(
            $this->clientId,
            $response->getContent('access_token'),
            $response->getContent('refresh_token')
        );
    }

    public function setRedirectUri(string $redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    public function refresh(ApiClientInterface $client, AuthenticatedAccount $account): AuthenticatedAccount
    {
        // TODO: Implement refresh() method.
    }
}