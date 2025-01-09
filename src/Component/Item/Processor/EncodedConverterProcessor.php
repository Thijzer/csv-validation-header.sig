<?php

namespace Misery\Component\Item\Processor;

use Misery\Component\Converter\ConverterInterface;
use Misery\Component\Encoder\ItemEncoder;

class EncodedConverterProcessor implements ProcessorInterface
{
    private $converter;
    private $encoder;

    public function __construct(
        ConverterInterface $converter,
        ItemEncoder $encoder
    ) {
        $this->converter = $converter;
        $this->encoder = $encoder;
    }

    public function process($item): array
    {
        return $this->converter->convert($this->encoder->encode($item));
    }
}