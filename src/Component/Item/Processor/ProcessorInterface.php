<?php

namespace Misery\Component\Item\Processor;

interface ProcessorInterface
{
    const IN = null;
    const OUT = null;
    public function process(array $item): array;
}