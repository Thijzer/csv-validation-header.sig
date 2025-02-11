<?php

namespace Misery\Component\Item\Processor;

use Misery\Component\Converter\ConverterInterface;
use Misery\Component\Decoder\ItemDecoder;

class DecodedRevertProcessor implements ProcessorInterface
{
    private $decoder;
    private $converter;

    public function __construct(
        ConverterInterface $converter,
        ItemDecoder $decoder
    ) {
        $this->decoder = $decoder;
        $this->converter = $converter;
    }

    public function process($item): array
    {
        return $this->decoder->decode($this->converter->revert($item));
    }
}