<?php

namespace Misery\Component\Common\Client;

use Misery\Component\Reader\ItemCollection;

abstract class PaginationCursor implements \Iterator
{
    protected ?string $link;
    protected ItemCollection $items;

    public function __construct(private ApiClientInterface $client, string $endpoint)
    {
        $this->link = $this->client->getUrlGenerator()->generate($endpoint);
        $this->items = new ItemCollection();
    }

    abstract protected function loadNextPage(): void;

    public function getClient(): ApiClientInterface
    {
        return $this->client;
    }

    public function current()
    {
        if (!$this->valid()) {
            return false;
        }

        return $this->items->current();
    }

    public function next(): void
    {
        $this->items->next();
    }

    public function key(): int
    {
        return $this->items->key();
    }

    public function valid(): bool
    {
        if (!$this->items->valid()) {
            $this->loadNextPage();
        }

        return $this->items->valid();
    }

    public function rewind(): void
    {
        $this->items->rewind();
    }
}