<?php

namespace Misery\Component\Akeneo\Client;

use Misery\Component\Common\Client\PaginationCursor;
use Misery\Component\Reader\ItemCollection;

class AkeneoPaginationCursor extends PaginationCursor
{
    protected function loadNextPage(): void
    {
        if (null === $this->link) {
            return;
        }

        $response = $this->getClient()
            ->get($this->link)
            ->getResponse();

        $content = $response->getContent();

        $this->link = $content['_links']['next']['href'] ?? null;
        $this->items = new ItemCollection($content['_embedded']['items'] ?? []);
    }
}