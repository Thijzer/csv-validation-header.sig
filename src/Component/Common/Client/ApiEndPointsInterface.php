<?php

namespace Misery\Component\Common\Client;

interface ApiEndPointsInterface
{
    public function getEndPoint(string $endpointName): ApiEndpointInterface;
}