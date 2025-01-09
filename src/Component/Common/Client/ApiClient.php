<?php

namespace Misery\Component\Common\Client;

use Misery\Component\Common\Generator\UrlGenerator;

class ApiClient implements ApiClientInterface
{
    private $handle;
    /** @var UrlGenerator */
    private $urlGenerator;
    /** @var AuthenticatedAccount */
    private $authenticatedAccount;
    /** @var array */
    private $headers = [];
    private ?ApiEndPointsInterface $endpoints = null;

    public function __construct(string $domain)
    {
        $this->urlGenerator = new UrlGenerator($domain);
    }

    public function authorize(ApiClientAccountInterface $account): void
    {
        if (null === $this->handle) {
            $this->handle = \curl_init();
            $this->endpoints = $account->getSupporterEndPoints();
            $this->authenticatedAccount = $account->authorize($this);
        }
    }

    public function getApiEndpoint(string $apiEndpoint): ApiEndpointInterface
    {
        return $this->endpoints->getEndPoint($apiEndpoint);
    }

    public function refreshToken(): void
    {
        $account = $this->authenticatedAccount->getAccount();
        $authenticatedAccount = $this->authenticatedAccount;
        $this->authenticatedAccount = null;
        $this->authenticatedAccount = $account->refresh($this, $authenticatedAccount);
    }

    /**
     * A GET HTTP VERB
     */
    public function search(string $endpoint, array $params = []): self
    {
        $this->setAuthenticationHeaders();
        $this->setHeaders(['Content-Type' => 'application/json']);

        \curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, "GET");
        \curl_setopt($this->handle, CURLOPT_URL, $endpoint . $this->urlGenerator->createParams($params));

        return $this;
    }

    /**
     * A GET HTTP VERB
     */
    public function get(string $endpoint): self
    {
        $this->setAuthenticationHeaders();
        $this->setHeaders(['Content-Type' => 'application/json']);

        \curl_setopt($this->handle, CURLOPT_URL, $endpoint);

        \curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, "GET");

        return $this;
    }

    /**
     * A POST HTTP VERB
     * $postData is a structured entity array that will be encoded to json
     */
    public function post(string $endpoint, array $postData): self
    {
        $this->setAuthenticationHeaders();
        $this->setHeaders(['Content-Type' => 'application/json']);

        \curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, "POST");
        \curl_setopt($this->handle, CURLOPT_URL, $endpoint);
        \curl_setopt($this->handle, CURLOPT_POST, true);
        \curl_setopt($this->handle, CURLOPT_POSTFIELDS, \json_encode($postData));

        return $this;
    }

    public function postXForm(string $endpoint, array $postData): self
    {
        $this->setAuthenticationHeaders();
        $this->setHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        \curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, "POST");
        \curl_setopt($this->handle, CURLOPT_URL, $endpoint);
        \curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($this->handle, CURLOPT_POST, true);
        \curl_setopt($this->handle, CURLOPT_POSTFIELDS, http_build_query($postData));

        return $this;
    }

    /**
     * HTTP PATCH VERB That supports a multi patch insert
     * max 100 inserts per request
     */
    public function multiPatch(string $endpoint, array $dataSet): self
    {
        $this->setAuthenticationHeaders();
        $this->setHeaders(['Content-Type' => 'application/vnd.akeneo.collection+json']);

        $patchData = "";
        foreach($dataSet as $item) {
            $patchData .= json_encode($item)."\n";
        }

        \curl_setopt($this->handle, CURLOPT_URL, $endpoint);
        \curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, "PATCH");
        \curl_setopt($this->handle, CURLOPT_POSTFIELDS, $patchData);

        return $this;
    }

    /**
     * HTTP PATCH VERB
     */
    public function patch(string $endpoint, array $patchData): self
    {
        $this->setAuthenticationHeaders();
        $this->setHeaders(['Content-Type' => 'application/json']);

        \curl_setopt($this->handle, CURLOPT_URL, $endpoint);
        \curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, "PATCH");
        \curl_setopt($this->handle, CURLOPT_POSTFIELDS, \json_encode($patchData));

        return $this;
    }

    /**
     * A DELETE HTTP VERB
     */
    public function delete(string $endpoint): self
    {
        $this->setAuthenticationHeaders();
        $this->setHeaders(['Content-Type' => 'application/json']);

        \curl_setopt($this->handle, CURLOPT_URL, $endpoint);
        \curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, "DELETE");

        return $this;
    }

    public function log(string $message, int $statusCode = null, $content): void
    {
        $message = sprintf("[%s] %s %s %s",
            date('Y-m-d H:i:s'),
            $message,
            $statusCode,
            json_encode($content)
        );

        file_put_contents(
            '/app/var/logs/curl.log',
            PHP_EOL . $message,
            FILE_APPEND
        );
    }

    /**
     * Set HTTP Headers
     */
    public function setHeaders(array $headerData): self
    {
        $this->headers = array_merge($this->headers, $headerData);

        return $this;
    }

    public function generateHeaders(): void
    {
        \curl_setopt($this->handle, CURLOPT_HTTPHEADER, array_map(function ($key, $value) {
            return $key . ': ' . $value;
        }, array_keys($this->headers), $this->headers));

        $this->headers = [];
    }

    /**
     * Returns a Usable API response
     */
    public function getResponse(): ApiResponse
    {
        $this->generateHeaders();

        \curl_setopt($this->handle, CURLOPT_HEADER, true);
        \curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);

        // obtain response
        $content = \curl_exec($this->handle);
        $status = \curl_getinfo($this->handle, CURLINFO_HTTP_CODE);

        // extract body
        $headerSize = curl_getinfo($this->handle, CURLINFO_HEADER_SIZE);
        $headers = substr($content, 0, $headerSize);
        $content = substr($content, $headerSize);
        $headers = $this->getResponseHeaders($headers);

        if (in_array($status, [200, 204]) && !$content) {
            return ApiResponse::create([], $status);
        }

        $multi = [];
        foreach (explode("\n", $content) as $c) {
            $multi[] = \json_decode($c, true);
        }

        if ($status === 404) {
            throw new Exception\PageNotFoundException($content['message'] ?? 'Page Not Found 404');
        }
        if ($status === 401) {
            throw new Exception\UnauthorizedException($content['message'] ?? 'Unauthorized');
        }

        if (!$content) {
            throw new \RuntimeException(curl_error($this->handle), curl_errno($this->handle));
        }
        $multi = array_filter($multi);

        if (count($multi) > 1) {
            return ApiResponse::createFromMulti($multi);
        }
        if (count($multi) === 1) {
            return ApiResponse::create($multi[0], $status, $headers);
        }

        return ApiResponse::create([], $status);
    }

    public function getPaginator(string $startUrl): PaginationCursor
    {
        return $this->authenticatedAccount->getAccount()->getPaginator($this, $startUrl);
    }

    private function getResponseHeaders($respHeaders): array
    {
        $headers = [];
        $headerText = substr($respHeaders, 0, strpos($respHeaders, "\r\n\r\n"));

        foreach (explode("\r\n", $headerText) as $i => $line) {
            if ($i === 0) {
                continue;
            } else {
                list ($key, $value) = explode(': ', $line);

                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    private function setAuthenticationHeaders(): void
    {
        if ($this->authenticatedAccount instanceof AuthenticatedAccount) {
            $this->authenticatedAccount->useToken($this);
        }
    }

    public function clear(): void
    {
        \curl_setopt($this->handle, \CURLOPT_HEADERFUNCTION, null);
        \curl_setopt($this->handle, \CURLOPT_READFUNCTION, null);
        \curl_setopt($this->handle, \CURLOPT_WRITEFUNCTION, null);
        \curl_setopt($this->handle, \CURLOPT_PROGRESSFUNCTION, null);
        \curl_reset($this->handle);
    }

    public function close(): void
    {
        if ($this->handle) {
            $this->clear();
            \curl_close($this->handle);
            $this->handle = null;
            $this->authenticatedAccount = null;
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    public function getUrlGenerator(): UrlGenerator
    {
        return $this->urlGenerator;
    }
}
