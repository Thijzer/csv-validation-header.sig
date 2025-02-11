<?php

namespace Misery\Component\Common\Client;

interface ApiEndpointInterface
{
    public function getAll(): string;

    public function getSingleEndPoint(): string;
}