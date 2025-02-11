<?php

namespace Misery\Component\Parser;

use Misery\Component\Common\Cursor\CursorInterface;

class XlsxParser implements CursorInterface
{
    /** @var string */
    private $filename;
    /** @var \Iterator */
    private $cursor;
    /** @var mixed|void */
    private $headers;
    /** @var int|null */
    private $count;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $this->init();
    }

    public static function create(string $filename): self
    {
        return new self($filename);
    }

    private function init(): void
    {
        // https://github.com/AsperaGmbH/xlsx-reader
        $reader = new \Aspera\Spreadsheet\XLSX\Reader();
        $reader->open($this->filename);
        $this->cursor = $reader;

        $this->headers = $this->cursor->current();
        $this->next();
    }

    public function current(): mixed
    {
        if (false === $this->cursor->valid()) {
            return false;
        }

        $current = $this->cursor->current();
        if ([] === $current || null === $this->headers) {
            return false;
        }

        // fill dragging empty cells
        $c1 = min(count($this->headers), count($current));

        return @array_combine(
            $this->headers,
            $current+array_fill($c1, count($this->headers)-$c1, null)
        );
    }

    public function next(): void
    {
        $this->cursor->next();
    }

    public function key(): mixed
    {
        return $this->cursor->key();
    }

    public function valid(): bool
    {
        return $this->cursor->valid();
    }

    public function rewind(): void
    {
        if (false === $this->valid()) {
            $this->count = $this->key() - 1;
        }

        $this->cursor->rewind();
    }

    public function count(): int
    {
        if (null === $this->count) {
            $this->loop(static function () {});
        }

        return $this->count;
    }

    public function loop(callable $callable): void
    {
        while ($this->valid()) {
            $callable($this->current());
            $this->next();
        }
        $this->rewind();
    }

    public function getIterator(): \Generator
    {
        while ($this->valid()) {
            yield $this->key() => $this->current();
            $this->next();
        }
        $this->rewind();
    }

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

    public function clear(): void
    {
        // TODO: Implement clear() method.
    }
}