<?php

namespace Misery\Component\Common\Cursor;

class FunctionalCursor implements CursorInterface
{
    private CursorInterface $cursor;
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
    public function current(): mixed
    {
        $function = $this->function;
        $item = $this->cursor->current();
        return $item ? $function($item) : false;
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
        return $this->cursor->valid();
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        $this->cursor->rewind();
    }

    /**
     * {@inheritDoc}
     */
    public function seek($offset): void
    {
        $this->cursor->seek($offset);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return $this->cursor->count();
    }

    public function clear(): void
    {
        $this->cursor->clear();
    }
}