<?php

namespace Misery\Component\Common\Client;

use Exception;
use Misery\Component\Common\Client\Endpoint\BasicApiEndpoints;
use Misery\Component\Common\Generator\UrlGenerator;

class BasicAuthApiClient implements ApiClientInterface
{
    private string $baseUri;
    private string $username;
    private string $password;
    private mixed $status;
    private mixed $response;
    private UrlGenerator $urlGenerator;
    private BasicApiEndpoints $endpoints;

    public function __construct($baseUri, $username, $password)
    {
        $this->baseUri = rtrim($baseUri, '/');
        $this->urlGenerator = new UrlGenerator($this->baseUri);
        $this->endpoints = new BasicApiEndpoints();

        $this->username = $username;
        $this->password = $password;
    }

    public function getApiEndpoint(string $apiEndpoint): ApiEndpointInterface
    {
        return $this->endpoints->getEndPoint($apiEndpoint);
    }

    public function sendRequest($method, $endpoint, $data = null, $headers = []): void
    {
        $endpoint = str_replace(' ', '%20', $endpoint); // TODO tmp fix replace spaces with url encoded value, fix for Coeck delta filter

        $curl = curl_init($endpoint);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $authHeader = 'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password);
        $headers[] = $authHeader;

        if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            $headers[] = 'Content-Type: application/json';
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $this->response = json_decode(curl_exec($curl), true);
        $this->status = \curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (curl_errno($curl)) {
            throw new Exception('Curl error: ' . curl_error($curl));
        }

        curl_close($curl);
    }

    public function getResponse(): ApiResponse
    {
        $response = null;

        if (is_array($this->response)) {
            $response = ApiResponse::create($this->response, $this->status);
        }
        if (is_string($this->response)) {
            $response = new ApiResponse($this->status, null, $this->response);
        }
        if (in_array($this->status, [200, 201, 204]) && !$this->response) {
            $response = ApiResponse::create([], $this->status);
        }

        $this->response = null;
        $this->status = null;

        if ($response instanceof ApiResponse) {
            return $response;
        }

        throw new \RuntimeException('Impossible NoApiResponse');
    }

    public function get($endpoint, $headers = []): self
    {
        $this->sendRequest('GET', $endpoint, null, $headers);

        return $this;
    }

    public function post($endpoint, $postData = null, $headers = []): self
    {
        $this->sendRequest('POST', $endpoint, $postData, $headers);

        return $this;
    }

    public function put($endpoint, $data = null, $headers = []): self
    {
        $this->sendRequest('PUT', $endpoint, $data, $headers);

        return $this;
    }

    public function delete($endpoint, $headers = []): self
    {
        $this->sendRequest('DELETE', $endpoint, null, $headers);

        return $this;
    }

    public function getUrlGenerator(): UrlGenerator
    {
        return $this->urlGenerator;
    }

    public function multiPatch(string $endpoint, array $dataSet): ApiClientInterface
    {
        // TODO: Implement multiPatch() method.
    }

    public function patch(string $endpoint, array $patchData): ApiClientInterface
    {
        // TODO: Implement patch() method.
    }

    public function log(string $message, int $statusCode = null, $content): void
    {
        // TODO: Implement log() method.
    }

    public function getPaginator(string $startUrl): PaginationCursor
    {
        // TODO: Implement getPaginator() method.
    }
}