<?php

namespace Misery\Component\Common\Cursor;

use Misery\Component\Item\ItemsFactoryIntoItem;

/**
 * CondensedCursor
 * Allows you to condense the cursor functionally into less lines
 */
class CondensedCursor implements CursorInterface
{
    /** @var CursorInterface */
    private $cursor;
    /** @var int|null */
    private $count;
    /** @var array */
    private $context;
    private $collection;
    /**
     * @var mixed|null
     */
    private $currentId;

    public function __construct(CursorInterface $cursor, array $context)
    {
        $this->cursor = $cursor;
        $this->context = $context;
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
    public function current(): mixed
    {
        while ($item = $this->cursor->current()) {
            $identifier = $item[$this->context['on']] ?? null;

            // init state
            if (null === $this->currentId) {
                $this->currentId = $identifier;
            }

            // new set
            if ($this->currentId !== $identifier && count($this->collection) > 0) {
                $this->currentId = $identifier;

                $collection = $this->releaseLastCollection();
                $this->collection[] = $item;

                return $collection;
            }

            $this->collection[] = $item;
            $this->cursor->next();
        }

        $collection = $this->releaseLastCollection();
        if (false === $item && $collection !== []) {
            return $collection;
        }

        return false;
    }

    private function releaseLastCollection(): array
    {
        $collection = $this->collection;

        // reset
        $this->collection = [];
        if (isset($this->context['spread'])) {
            return ItemsFactoryIntoItem::spreadFromConfig($collection, $this->context);
        }

        if (isset($this->context['join_into'])) {
            return ItemsFactoryIntoItem::createFromConfig($collection, $this->context['join_into']);
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        $this->cursor->next();
    }

    /**
     * {@inheritDoc}
     */
    public function key(): mixed
    {
        return $this->cursor->key();
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
        $this->collection = null;
        $this->currentId = null;

        $this->cursor->clear();
    }
}