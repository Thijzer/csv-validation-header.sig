<?php

namespace Misery\Component\Reader;

interface ReaderInterface
{
    /** @return array|false */
    public function read();
    public function getIterator(): \Iterator;
    public function map(callable $callable): self;
    public function getItems(): array;

    // each part find, sort and filter should be part of it's own interface
    // these interfaces are not part of the ReaderInterface
    public function find(array $constraints): self;
    public function filter(callable $callable): self;

    /**
     * Allow the reader to be reset or cleared of all memory data
     * We do not store data in the reader but our components might
     * so passing the function is key
     */
    public function clear(): void;
}