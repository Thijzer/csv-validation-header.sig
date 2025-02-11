<?php

namespace Misery\Component\Item\Builder;

use Misery\Component\Filter\ColumnReducer;
use Misery\Component\Reader\ReaderInterface;

class ReferenceBuilder
{
    public static function build(ReaderInterface $reader, string ...$references)
    {
        $concat = [];
        foreach (ColumnReducer::reduce($reader, ...$references)->getIterator() as $index => $array) {
            $concat[implode('-', array_keys($array))][$index] = implode('-', array_values($array));
        }

        return $concat;
    }

    public static function buildValues(ReaderInterface $reader, string ...$references)
    {
        return current(static::build($reader, ...$references));
    }

    public static function buildIndexList(ReaderInterface $reader, string ...$references):array
    {
        $tmp = [];
        foreach (self::buildValues($reader, ...$references) as $index => $code) {
            $tmp[(string) $code][$index] = $code;
        }

        return $tmp;
    }
}