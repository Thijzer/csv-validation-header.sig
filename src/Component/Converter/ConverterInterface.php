<?php

namespace Misery\Component\Converter;

interface ConverterInterface
{
    public function convert(array $item): array;
    public function revert(array $item): array;
}