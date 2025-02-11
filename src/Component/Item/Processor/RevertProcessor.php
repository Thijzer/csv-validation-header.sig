<?php

namespace Misery\Component\Item\Processor;

use Misery\Component\Converter\ConverterInterface;

class RevertProcessor implements ProcessorInterface
{
    private $converter;

    public function __construct(ConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    public function process($item): array
    {
        return $this->converter->revert($item);
    }
}