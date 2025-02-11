<?php

namespace Misery\Component\Item\Builder;

use Misery\Component\Filter\ColumnReducer;
use Misery\Component\Reader\ReaderInterface;

class KeyValuePairBuilder
{
    public static function build(
        ReaderInterface $reader,
        string $key,
        string $value,
        string $keyPrefix
    )
    {
        $concat = [];
        foreach (ColumnReducer::reduce($reader, $key, $value, $keyPrefix)->getIterator() as $array) {
            if (!empty($keyPrefix)) {
                $concat[$array[$keyPrefix] . '-' . $array[$key]] = $array[$value];

                continue;
            }

            $concat[$array[$key]] = $array[$value];
        }

        return $concat;
    }
}