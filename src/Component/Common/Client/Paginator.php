<?php

namespace Misery\Component\Common\Client;

use Misery\Component\Common\Client\Exception\UnauthorizedException;
use Misery\Component\Reader\ItemCollection;

//TODO rename to akeneo Paginator
class Paginator
{
    private $client;
    private $first;
    private $previous;
    private $next;
    private $count;
    private $items;

    public function __construct(
        ApiClientInterface $client,
        ItemCollection $items,
        string $first = null,
        string $previous = null,
        string $next = null,
        int $count = null
    ) {
        $this->client = $client;
        $this->first = $first;
        $this->previous = $previous;
        $this->next = $next;
        $this->count = $count;
        $this->items = $items;
    }

    public static function create(ApiClientInterface $client, array $data): self
    {
        return new self(
            $client,
            new ItemCollection($data['_embedded']['items'] ?? []),
            $data['_links']['first']['href'] ?? null,
            $data['_links']['previous']['href'] ?? null,
            $data['_links']['next']['href'] ?? null,
            $data['items_count'] ?? null
        );
    }

    /**
     * Returns the page given a complete uri.
     */
    protected function getPage(string $uri): self
    {
        try {
            $data = $this->client->get($uri)->getResponse()->getContent();
        } catch (UnauthorizedException $e) {
            $this->client->refreshToken();
            $data = $this->client->get($uri)->getResponse()->getContent();
        }

        return self::create($this->client, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstPage(): self
    {
        return $this->getPage($this->first);
    }

    /**
     * {@inheritdoc}
     */
    public function getPreviousPage(): ?self
    {
        return $this->hasPreviousPage() ? $this->getPage($this->previous) : null;
    }

    public function getNextPage(): ?self
    {
        return $this->hasNextPage() ? $this->getPage($this->next) : null;
    }

    public function getClient(): ApiClient
    {
        return $this->client;
    }

    public function getFirst(): ?string
    {
        return $this->first;
    }

    public function getPrevious(): ?string
    {
        return $this->previous;
    }

    public function getNext(): ?string
    {
        return $this->next;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function getItems(): ItemCollection
    {
        return $this->items;
    }

    public function hasNextPage(): bool
    {
        return null !== $this->next;
    }

    public function hasPreviousPage(): bool
    {
        return null !== $this->previous;
    }
}