<?php

namespace Misery\Component\Reader;

use Misery\Component\Common\Cursor\CursorInterface;

class ItemCollection implements CursorInterface
{
    /** @var int */
    private $position = 0;
    /** @var array */
    private $items;
    /** @var array */
    private $keys;

    public function __construct(array $items = [])
    {
        // array_values removes any position keys
        $this->items = $items;
        $this->keys = array_keys($items);
    }

    public function add(array $items)
    {
        foreach ($items as $key => $value) {
            $this->items[$key] = $value;
        }
        $this->keys = array_keys($this->items);
    }

    /**
     * @param string|int $key
     * @param mixed $value
     */
    public function set($key, $value): void
    {
        $this->items[$key] = $value;
        $this->keys = array_keys($this->items);
    }

    /**
     * {@inheritDoc}
     */
    public function loop(callable $callable): void
    {
        foreach ($this->getIterator() as $row) {
            $callable($row);
        }
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
        return current($this->items) ?? $this->valid();
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        ++$this->position;
        next($this->items);
    }

    /**
     * {@inheritDoc}
     */
    public function key(): mixed
    {
        return $this->keys[$this->position] ?? $this->position;
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return $this->position < $this->count();
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        reset($this->items);
        $this->position = 0;
    }

    /**
     * {@inheritDoc}
     */
    public function seek($pointer): void
    {
        $this->rewind();
        while ($this->valid()) {
            if ($this->key() === $pointer) {
                break;
            }
            $this->next();
        }

        if (!$this->valid()) {
            //throw new OutOfBoundsException('Invalid position');
        }
    }

    public function getItemValues(string $key)
    {
        $items = [];
        foreach ($this->getIterator() as $index => $row) {
            $items[$index] = $row[$key];
        }

        return $items;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return \count($this->items);
    }

    public function clear(): void
    {
        $this->items = [];
    }
}
