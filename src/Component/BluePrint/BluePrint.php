<?php

namespace Misery\Component\BluePrint;

use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Converter\ConverterInterface;
use Misery\Component\Decoder\ItemDecoder;
use Misery\Component\Encoder\ItemEncoder;

class BluePrint implements RegisteredByNameInterface
{
    private $name;
    private $encoder;
    private $decoder;
    private $converter;
    private $filenames;

    public function __construct(
        string $name,
        ItemEncoder $encoder,
        ItemDecoder $decoder,
        ConverterInterface $converter = null,
        array $filenames = []
    ) {
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->name = $name;
        $this->converter = $converter;
        $this->filenames = $filenames;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFilenames(): array
    {
        return $this->filenames;
    }

    public function getEncoder(): ItemEncoder
    {
        return $this->encoder;
    }

    public function getConverter(): ?ConverterInterface
    {
        return $this->converter;
    }

    public function getDecoder(): ItemDecoder
    {
        return $this->decoder;
    }
}