<?php

namespace Misery\Component\Filter;

use Misery\Component\Reader\ReaderInterface;

/**
 * / input
 *  [
 *       'id' => "3",
 *       'first_name' => 'Gordie',
 *       'last_name' => 'Ramsey',
 *       'phone' => '1234556',
 *  ],
 *  [
 *       'id' => "3",
 *       'first_name' => 'Gordie',
 *       'last_name' => 'Ramsey',
 *       'phone' => '1234556',
 *  ],
 *
 * ColumnRemover::remove($reader, 'first_name');
 *
 * / output
 *  [
 *       'id' => "3",
 *       'last_name' => 'Ramsey',
 *       'phone' => '1234556',
 *  ],
 *  [
 *       'id' => "3",
 *       'last_name' => 'Ramsey',
 *       'phone' => '1234556',
 *  ],
 *
 */
class ColumnRemover
{
    public static function remove(ReaderInterface $reader, string ...$columnNames): ReaderInterface
    {
        return $reader
            ->map(static function(array $row) use ($columnNames) {
                $nRow = $row;
                foreach ($columnNames as $key) {
                    unset($nRow[$key]);
                }

                return $nRow;
            })
        ;
    }
}