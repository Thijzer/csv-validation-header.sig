<?php

namespace Misery\Component\Common\Client;

use Misery\Component\Common\Generator\UrlGenerator;

interface ApiClientInterface
{
    /**
     * A GET HTTP VERB
     */
    public function get(string $endpoint): self;
    /**
     * A POST HTTP VERB
     * $postData is a structured entity array that will be encoded to json
     */
    public function post(string $endpoint, array $postData): self;
    /**
     * HTTP PATCH VERB That supports a multi patch insert
     * max 100 inserts per request
     */
    public function multiPatch(string $endpoint, array $dataSet): self;
    /**
     * HTTP PATCH VERB
     */
    public function patch(string $endpoint, array $patchData): self;
    /**
     * A DELETE HTTP VERB
     */
    public function delete(string $endpoint): self;
    public function getResponse(): ApiResponse;
    public function log(string $message, int $statusCode = null, $content): void;

    public function getUrlGenerator(): UrlGenerator;

    public function getPaginator(string $startUrl): PaginationCursor;

    public function getApiEndpoint(string $apiEndpoint): ApiEndpointInterface;
}
