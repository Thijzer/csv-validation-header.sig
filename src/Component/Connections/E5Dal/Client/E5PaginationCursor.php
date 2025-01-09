<?php

namespace Misery\Component\Connections\E5Dal\Client;

use Misery\Component\Common\Client\PaginationCursor;
use Misery\Component\Reader\ItemCollection;

class E5PaginationCursor extends PaginationCursor
{
    protected function loadNextPage(): void
    {
        if (null === $this->link) {
            return;
        }

        $response = $this->getClient()
            ->get($this->link)
            ->getResponse();

        $this->link = null;
        $this->items = new ItemCollection($response->getContent());

        // Use a regular expression to extract the URL
        $pattern = '/<([^>]*)>; rel=next/';
        $linkHeader = $response->getHeaders('link');
        if (preg_match($pattern, $linkHeader, $matches)) {
            $url = $matches[1];
            $this->link = $url;
        }
    }
}