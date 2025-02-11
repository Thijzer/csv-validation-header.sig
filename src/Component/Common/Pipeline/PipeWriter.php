<?php

namespace Misery\Component\Common\Pipeline;

use Misery\Component\Writer\ItemWriterInterface;

class PipeWriter implements PipeWriterInterface
{
    private $writer;

    public function __construct(ItemWriterInterface $writer)
    {
        $this->writer = $writer;
    }
    public function write(array $data): void
    {
        $this->writer->write($data);
    }

    public function stop(): void
    {
        $this->writer->close();
    }
}