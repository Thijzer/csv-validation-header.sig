<?php

namespace Misery\Component\Parser;

use ArrayIterator;

class JsonFileParser extends FileParser
{
    private ArrayIterator $iterator;

    /**
     * Constructor.
     *
     * @param string $filename The path to the JSON file.
     */
    public function __construct(\SplFileObject $file, string $container = null)
    {
        if (!file_exists($filename = $file->getRealPath())) {
            throw new \RuntimeException("File not found: $filename");
        }

        $content = file_get_contents($filename);
        $bom = pack('H*', 'EFBBBF');
        $content = preg_replace("/^$bom/", '', $content);
        $parsed = \json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Failed to parse JSON: " . json_last_error_msg());
        }

        if ($container) {
            $parsed = $parsed[$container] ?? [];
        }

        $this->iterator = new ArrayIterator($parsed);

        parent::__construct($file);
    }

    public static function create(string $filename, string $container = null): self
    {
        if (!file_exists($filename)) {
            throw new \RuntimeException("File not found: $filename");
        }

        return new self(new \SplFileObject($filename), $container);
    }

    /**
     * Returns the current element in the array.
     *
     * @return mixed
     */
    public function current(): mixed
    {
        if ($this->iterator->valid()) {
            return $this->iterator->current();
        }

        return false;
    }

    /**
     * Moves to the next element.
     */
    public function next(): void
    {
        $this->iterator->next();
    }

    /** Returns the current key (index).*/
    public function key(): string|int
    {
        return $this->iterator->key();
    }

    /**
     * Checks if the current position is valid.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->iterator->valid();
    }

    /**
     * Rewinds to the start of the array.
     */
    public function rewind(): void
    {
        $this->iterator->rewind();
    }
}