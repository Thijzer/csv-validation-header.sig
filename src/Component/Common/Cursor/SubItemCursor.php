<?php

namespace Misery\Component\Common\Cursor;

use Misery\Component\Converter\ItemCollectionLoaderInterface;
use Misery\Component\Reader\ItemCollection;
use Misery\Component\Reader\ItemReader;

class SubItemCursor implements \Iterator
{
    private $subItems;

    public function __construct(private \Iterator $iterator, private ItemCollectionLoaderInterface $subReader)
    {
        $this->subItems = null;
    }

    public function current(): bool|array
    {
        if (null === $this->subItems) {
            $subItem = $this->iterator->current();
            if (false === $subItem) {
                return false;
            }

            $this->subItems = $this->subReader->load($subItem);
        }

        if ($item = $this->subItems->current()) {
            return $item;
        }

        // reset and move to next
        $this->iterator->next();
        $this->subItems = null;

        return $this->current();
    }

    public function next(): void
    {
        $this->subItems->next();
    }

    public function key()
    {
        return $this->iterator->key();
    }

    public function valid(): bool
    {
        return $this->current() !== false;
    }

    public function rewind(): void
    {
        $this->iterator->rewind();
        $this->subItems = $this->subReader->load($this->iterator->current());
    }
}