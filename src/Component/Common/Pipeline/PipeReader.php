<?php

namespace Misery\Component\Common\Pipeline;

use Misery\Component\Reader\ReaderInterface;

class PipeReader implements PipeReaderInterface
{
    private ReaderInterface $reader;

    public function __construct(ReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    public function read()
    {
        return $this->reader->read();
    }

    public function stop(): void
    {
        $this->reader->clear();
    }
}