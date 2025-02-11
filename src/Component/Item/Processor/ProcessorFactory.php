<?php

namespace Misery\Component\Item\Processor;

use Misery\Component\Converter\ConverterInterface;
use Misery\Component\Decoder\ItemDecoder;
use Misery\Component\Encoder\ItemEncoder;

class ProcessorFactory
{
    public function create($subjectA, $subjectB = null, string $direction = null): ProcessorInterface
    {
        switch (true) {
            case $direction === ProcessorInterface::IN && $subjectA instanceof ConverterInterface && $subjectB instanceof ItemEncoder:
                return new EncodedConverterProcessor($subjectA, $subjectB);
            case $direction === ProcessorInterface::IN && $subjectA instanceof ConverterInterface && null === $subjectB:
                return new ConverterProcessor($subjectA);
            case $direction === ProcessorInterface::OUT && $subjectA instanceof ConverterInterface && $subjectB instanceof ItemDecoder:
                return new DecodedRevertProcessor($subjectA, $subjectB);
            case $direction === ProcessorInterface::OUT && $subjectA instanceof ConverterInterface && null === $subjectB:
                return new RevertProcessor($subjectA);
            default:
                return new NullProcessor();
        }
    }
}