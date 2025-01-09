<?php

namespace Misery\Component\Item\Processor;

use Misery\Component\Encoder\ItemEncoder;

class EncoderProcessor implements ProcessorInterface
{
    private $encoder;

    public function __construct(ItemEncoder $encoder)
    {
        $this->encoder = $encoder;
    }

    public function process($item): array
    {
        return $this->encoder->encode($item);
    }
}