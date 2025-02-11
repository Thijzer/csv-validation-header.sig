<?php

namespace Misery\Component\Common\Client;

class AuthenticatedAccount
{
    private $username;
    /** @var string|null */
    private $refreshToken;
    private $accessToken;
    /** @var string|null */
    private $expiresIn;
    /** @var int|null */
    private $expireTime;
    /** @var ApiClientAccountInterface */
    private $account;

    public function __construct(
        ApiClientAccountInterface $account,
        string $username,
        string $accessToken = null,
        string $refreshToken = null,
        string $expiresIn = null
    ){
        $this->username = $username;
        $this->refreshToken = $refreshToken;
        $this->accessToken = $accessToken;
        $this->expiresIn = $expiresIn;
        $this->expireTime = $this->expiresIn ? time() + $this->expiresIn: null;
        $this->account = $account;
    }

    public function getAccount(): ApiClientAccountInterface
    {
        return $this->account;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function isExpired(): bool
    {
        return $this->expiresIn !== null && time() > $this->expireTime;
    }

    public function invalidate(): void
    {
        $this->expiresIn = null;
    }

    public function useToken(ApiClientInterface $client): void
    {
        if ($this->accessToken) {
            $client->setHeaders([
                'Authorization' => 'Bearer '. $this->accessToken,
            ]);
        }
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }
}