<?php

namespace Misery\Component\Common\Generator;

use Misery\Component\Common\Utils\ValueFormatter;

class UrlGenerator implements GeneratorInterface
{
    private $url;
    private $domain;

    public function __construct(string $domain)
    {
        $this->url = rtrim($domain, DIRECTORY_SEPARATOR);
        $this->domain = $this->url;
    }

    public function append(string $endpoint): void
    {
        $this->url = $this->domain . DIRECTORY_SEPARATOR.\ltrim($endpoint, DIRECTORY_SEPARATOR);
    }

    public function format(string $endPoint, ...$data): string
    {
        $endPoint = DIRECTORY_SEPARATOR.\ltrim($endPoint, DIRECTORY_SEPARATOR);

        return $this->url . ValueFormatter::recursiveFormat($endPoint, $data);
    }

    public function generate(string $endPoint, ...$data): string
    {
        $endPoint = DIRECTORY_SEPARATOR.\ltrim($endPoint, DIRECTORY_SEPARATOR);

        return sprintf($this->url . $endPoint, ...$data);
    }

    public function generateFromDomain(string $endPoint): string
    {
        $endPoint = DIRECTORY_SEPARATOR.\ltrim($endPoint, DIRECTORY_SEPARATOR);

        return $this->domain . $endPoint;
    }

    public function createParams(array $params): string
    {
        return $params !== [] ? '?' . \http_build_query($params) : '';
    }
}