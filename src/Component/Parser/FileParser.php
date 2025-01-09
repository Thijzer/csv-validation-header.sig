<?php

namespace Misery\Component\Parser;

use Misery\Component\Common\Cursor\CursorInterface;

/**
 * Abstract line by line Parser
 */
abstract class FileParser implements CursorInterface
{
    /** @var int|null */
    private $count;
    /** @var \SplFileObject */
    protected $file;

    public function __construct(\SplFileObject $file)
    {
        if ($file->isFile()) {
            $this->file = $file;

            #ini_set('auto_detect_line_endings', '1');
        }
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
        $this->count = $this->key();

        $this->rewind();
    }

    /**
     * {@inheritDoc}
     *
     * @return false|string
     */
    public function current(): mixed
    {
        return $this->file->current();
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        $this->file->next();
    }

    /**
     * {@inheritDoc}
     */
    public function key(): mixed
    {
        return $this->file->key();
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return $this->file->valid();
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        $this->file->rewind();
    }

    /**
     * {@inheritDoc}
     */
    public function seek($offset): void
    {
        $this->file->seek($offset);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        if (null === $this->count) {
            $this->loop(static function (){});
        }

        return $this->count;
    }

    public function clear(): void
    {
        // TODO: Implement clear() method.
    }
}