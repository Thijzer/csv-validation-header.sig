<?php

namespace Misery\Component\Item\Processor;

class NullProcessor implements ProcessorInterface
{
    public function process(array $item): array
    {
        return $item;
    }
}