<?php

namespace Tests\Misery\Component\Filter;

use Misery\Component\Reader\ItemCollection;
use Misery\Component\Reader\ItemReader;
use PHPUnit\Framework\TestCase;
use Misery\Component\Filter\ColumnRemover;

class ColumnRemoverTest extends TestCase
{
    public function testRemove()
    {
        // Define sample input data
        $inputData = [
            [
                'id' => "3",
                'first_name' => 'Gordie',
                'last_name' => 'Ramsey',
                'phone' => '1234556',
            ],
            [
                'id' => "3",
                'first_name' => 'Gordie',
                'last_name' => 'Ramsey',
                'phone' => '1234556',
            ],
        ];

        $reader = new ItemReader(new ItemCollection($inputData));

        // Define the column names to remove
        $columnNames = ['first_name'];

        // Call the remove method
        $filteredData = ColumnRemover::remove($reader, ...$columnNames);

        // Define the expected output
        $expectedOutput = [
            [
                'id' => "3",
                'last_name' => 'Ramsey',
                'phone' => '1234556',
            ],
            [
                'id' => "3",
                'last_name' => 'Ramsey',
                'phone' => '1234556',
            ],
        ];

        // Assert that the result matches the expected output
        $this->assertEquals($expectedOutput, $filteredData->getItems());
    }
}
