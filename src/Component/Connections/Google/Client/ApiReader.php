<?php

namespace Misery\Component\Connections\Google\Client;

use Misery\Component\Common\Client\ApiClient;
use Misery\Component\Common\Client\ApiClientInterface;
use Misery\Component\Common\Client\ApiEndpointInterface;
use Misery\Component\Common\Client\Paginator;
use Misery\Component\Reader\ItemReader;
use Misery\Component\Reader\ReaderInterface;

class ApiReader implements ReaderInterface
{
    private $client;
    private $page;
    private $endpoint;

    public function __construct(ApiClientInterface $client, ApiEndpointInterface  $endpoint)
    {
        $this->client = $client;
        $this->endpoint = $endpoint;
    }

    private function all(int $pageSize = 10, array $queryParameters = []): array
    {
        return $this->client
            ->get($this->client->getUrlGenerator()->generate($this->endpoint->getAll()))
            ->getResponse()
            ->getContent()
        ;
    }

    public function read()
    {
        if (null === $this->page) {
            $this->page = Paginator::create($this->client, $this->all());
        }
        $item = $this->page->getItems()->current();

        if (!$item) {
            $this->page = $this->page->getNextPage();
            if (!$this->page) {
                return false;
            }
            $item = $this->page->getItems()->current();
        }
        $this->page->getItems()->next();

        unset($item['_links']);

        return $item;
    }

    public function getIterator(): \Iterator
    {
        while ($item = $this->read()) {
            yield $item;
        }
    }

    public function find(array $constraints): ReaderInterface
    {
        // TODO we need to implement a find or search int the API
        $reader = $this;
        foreach ($constraints as $columnName => $rowValue) {
            if (is_string($rowValue)) {
                $rowValue = [$rowValue];
            }

            $reader = $reader->filter(static function ($row) use ($rowValue, $columnName) {
                return in_array($row[$columnName], $rowValue);
            });
        }

        return $reader;
    }

    public function filter(callable $callable): ReaderInterface
    {
        return new ItemReader($this->processFilter($callable));
    }

    private function processFilter(callable $callable): \Generator
    {
        foreach ($this->getIterator() as $key => $row) {
            if (true === $callable($row)) {
                yield $key => $row;
            }
        }
    }

    public function map(callable $callable): ReaderInterface
    {
        return new ItemReader($this->processMap($callable));
    }

    private function processMap(callable $callable): \Generator
    {
        foreach ($this->getIterator() as $key => $row) {
            yield $key => $callable($row);
        }
    }

    public function getItems(): array
    {
        return iterator_to_array($this->getIterator());
    }
}
