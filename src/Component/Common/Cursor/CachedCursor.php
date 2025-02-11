<?php

namespace Misery\Component\Common\Cursor;

class CachedCursor implements CursorInterface
{
    # larger caches might break the memory limit so be alert
    const SMALL_CACHE_SIZE = 1000;
    const MEDIUM_CACHE_SIZE = 5000;
    const LARGE_CACHE_SIZE = 10000;
    const EXTRA_LARGE_CACHE_SIZE = 50000;

    /** @var int|mixed */
    private $position = 0;
    /** @var CursorInterface */
    private $cursor;
    /** @var array */
    private $items = [];

    /** @var array */
    private $options = [
        'cache_size' => self::MEDIUM_CACHE_SIZE,
    ];
    /** @var array */
    private $range = [];

    public function __construct(CursorInterface $cursor, array $options = [])
    {
        $this->cursor = $cursor;
        $this->options = array_merge($this->options, $options);
        $this->position = $cursor->key();
    }

    public static function create(CursorInterface $cursor, array $options = []): self
    {
        return new self($cursor, $options);
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
        while ($item = $this->current()) {
            yield $this->key() => $item;
            $this->next();
        }
        $this->rewind();
    }

    /**
     * {@inheritDoc}
     */
    public function current(): mixed
    {
        $this->prefetch($this->position);

        return $this->items[$this->position] ?? false;
    }

    /**
     * Cursor is not rewind
     * so it could keep fetches in chunks without reset
     * @param int $i position
     */
    private function prefetch(int $i): void
    {
        if (!isset($this->range[$i])) {
            $this->range = array_flip(range($i,$i + $this->options['cache_size']-1));
            $this->items = [];
            while ($row = $this->cursor->current()) {
                if (isset($this->range[$this->cursor->key()])) {
                    $this->items[$this->cursor->key()] = $row;
                    if (\count($this->items) === $this->options['cache_size']) {
                        break;
                    }
                }
                $this->cursor->next();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * {@inheritDoc}
     */
    public function key(): mixed
    {
        return $this->position;
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
        $this->cursor->rewind();
        $this->position = $this->cursor->key();
    }

    /**
     * {@inheritDoc}
     */
    public function seek($pointer): void
    {
        $this->position = (int) $pointer;
        if (!$this->valid()) {
            //throw new OutOfBoundsException('Invalid position');
        }
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
        $this->items = [];
        $this->range = [];
        $this->rewind();
        $this->cursor->clear();
    }
}