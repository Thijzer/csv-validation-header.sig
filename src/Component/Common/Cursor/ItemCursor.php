<?php

namespace Misery\Component\Common\Cursor;

use Misery\Component\Reader\ItemReaderInterface;

class ItemCursor implements CursorInterface
{
    /** @var ItemReaderInterface */
    private $itemReader;

    public function __construct(ItemReaderInterface $itemReader)
    {
        $this->itemReader = $itemReader;
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
        return $this->itemReader->getIterator()->current();
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        $this->itemReader->getIterator()->next();
    }

    /**
     * {@inheritDoc}
     */
    public function key(): mixed
    {
        return $this->itemReader->getIterator()->key();
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return $this->itemReader->getIterator()->valid();
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        $this->itemReader->getIterator()->rewind();
    }

    /**
     * {@inheritDoc}
     */
    public function seek($pointer): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
    }

    public function clear(): void
    {
        $this->itemReader->clear();
    }
}