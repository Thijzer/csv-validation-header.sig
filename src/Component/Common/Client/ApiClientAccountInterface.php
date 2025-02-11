<?php

namespace Misery\Component\Common\Client;

interface ApiClientAccountInterface
{
    public function authorize(ApiClientInterface $client): AuthenticatedAccount;
    public function refresh(ApiClientInterface $client, AuthenticatedAccount $account): AuthenticatedAccount;
    public function getSupporterEndPoints(): ApiEndPointsInterface;
}