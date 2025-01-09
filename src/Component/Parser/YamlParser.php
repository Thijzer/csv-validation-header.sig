<?php

namespace Misery\Component\Parser;

use Assert\Assertion;
use Misery\Component\Common\Cursor\CursorInterface;
use Misery\Component\Reader\ItemCollection;
use Symfony\Component\Yaml\Yaml;

class YamlParser implements CursorInterface
{
    /** @var \SplFileObject */
    private $file;
    /** @var ItemCollection */
    private $cursor;

    public function __construct(\SplFileObject $file)
    {
        Assertion::file($file->getRealPath());
        $this->file = $file;
        $this->cursor = new ItemCollection();
    }

    public static function create(string $filename): self
    {
        return new self(new \SplFileObject($filename));
    }

    public function getCursor(): ItemCollection
    {
        if ($this->cursor->count() === 0) {
            $this->cursor->add(
                Yaml::parseFile($this->file->getRealPath())
            );
        }
         return $this->cursor;
    }

    public function current(): mixed
    {
        return $this->getCursor()->current();
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
        return $this->getCursor()->valid();
    }

    public function rewind(): void
    {
        $this->cursor->rewind();
    }

    public function count(): int
    {
        return $this->getCursor()->count();
    }

    public function loop(callable $callable): void
    {
        foreach ($this->getIterator() as $row) {
            $callable($row);
        }
    }

    public function getIterator(): \Generator
    {
        while ($this->valid()) {
            yield $this->key() => $this->current();
            $this->next();
        }
        $this->rewind();
    }

    public function seek($offset): void
    {
        $this->getCursor()->seek($offset);
    }

    public function clear(): void
    {
        // TODO: Implement clear() method.
    }
}