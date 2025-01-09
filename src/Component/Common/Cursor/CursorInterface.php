<?php

namespace Misery\Component\Common\Cursor;

interface CursorInterface extends \SeekableIterator, \Countable
{
    /**
     * Iterate over items  with a Generator and do something
     * @param callable $callable
     */
    public function loop(callable $callable): void;

    /**
     * Iterate over items with a Generator
     *
     * @return \Generator
     */
    public function getIterator(): \Generator;

    public function clear(): void;
}