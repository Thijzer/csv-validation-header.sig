<?php

namespace Misery\Component\Item\Processor;

use Misery\Component\Decoder\ItemDecoder;

class DecoderProcessor implements ProcessorInterface
{
    private $decoder;

    public function __construct(ItemDecoder $decoder)
    {
        $this->decoder = $decoder;
    }

    public function process($item): array
    {
        return $this->decoder->decode($item);
    }
}