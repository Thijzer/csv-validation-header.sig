<?php

namespace Misery\Component\Common\Cursor;

class TraversableCursor implements \Iterator
{
    private $places;
    private $count = 0;
    private $index = 0;

    public function __construct(\Traversable $places)
    {
        $this->places = $places;
    }

    public function current()
    {
        return $this->places[$this->index];
    }
    public function next()
    {
        $this->index++;
    }
    public function rewind()
    {
        $this->index = 0;
    }
    public function key()
    {
        return $this->index;
    }
    public function valid()
    {
        return isset($this->places[$this->key()]);
    }

    public function count(): int
    {
        return $this->count;
    }
}