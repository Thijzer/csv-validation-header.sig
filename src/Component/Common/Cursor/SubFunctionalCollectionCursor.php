<?php

namespace Misery\Component\Common\Cursor;

use Misery\Component\Reader\ItemCollection;

/**
 * SubFunctionalCollectionCursor
 * allows to subdivide a single item in the loop into sub-items reflecting the same functionality
 *
 * subdivision happen inside the current method
 *
 * key can never really return to subdivision key us subkey in conjunction with key.
 * - key = 1 [line_number]
 * - subkey = 10 [collum_number]
 */
class SubFunctionalCollectionCursor implements CursorInterface
{
    /** @var CursorInterface */
    private $cursor;
    /** @var ItemCollection|null */
    private $subItems;
    /** @var int|null */
    private $count;
    /** @var callable */
    private $function;

    public function __construct(CursorInterface $cursor, callable $function)
    {
        $this->cursor = $cursor;
        $this->function = $function;
    }

    /**
     * @inheritDoc
     */
    public function loop(callable $callable): void
    {
        while ($this->valid()) {
            $callable($this->current());
            $this->next();
        }
        $this->rewind();
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \Generator
    {
        while ($this->valid()) {
            yield $this->key() => $this->current();
            $this->next();
        }
        $this->rewind();
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        if (null === $this->subItems) {
            $subItem = $this->cursor->current();
            if (false === $subItem) {
                return false;
            }

            $function = $this->function;
            $this->subItems = $function($subItem);
        }

        if ($item = $this->subItems->current()) {
            return $item;
        }

        // reset and move to next
        $this->cursor->next();
        unset($this->subItems);

        return $this->current();
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        if ($this->subItems instanceof ItemCollection) {
            $this->subItems->next();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return $this->cursor->key();
    }

    public function subkey()
    {
        return $this->subItems instanceof ItemCollection ? $this->subItems->key(): false;
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return $this->current() !== false;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        if (false === $this->valid()) {
            $this->count = $this->key() - 1;
        }

        $this->cursor->rewind();
        $this->subItems = null;
    }

    /**
     * {@inheritDoc}
     */
    public function seek($pointer): void
    {
        $this->cursor->seek($pointer);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        if (null === $this->count) {
            $this->loop(static function () {});
        }

        return $this->count;
    }

    public function clear(): void
    {
        $this->count = null;
        $this->cursor->clear();
    }
}