<?php

namespace Misery\Component\Common\Pipeline;

class StandardPipe implements PipeInterface
{
    private $processor;

    public function __construct($actionProcessor)
    {

        $this->processor = $actionProcessor;
    }

    public function pipe(array $item): array
    {
        return $this->processor->process($item);
    }
}