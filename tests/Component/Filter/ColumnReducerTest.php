<?php

namespace Tests\Component\Filter;

use Misery\Component\Reader\ItemCollection;
use Misery\Component\Reader\ItemReader;
use PHPUnit\Framework\TestCase;
use Misery\Component\Filter\ColumnReducer;

class ColumnReducerTest extends TestCase
{
    public function testReduce()
    {
        // Define sample input data
        $inputData = [
            [
                'id' => "2",
                'first_name' => 'Mieke',
                'last_name' => 'Cauter',
                'phone' => '1234556356',
            ],
            [
                'id' => "3",
                'first_name' => 'Gordie',
                'last_name' => 'Ramsey',
                'phone' => '1234556',
            ],
        ];

        // Create a ItemReader
        $reader = new ItemReader(new ItemCollection($inputData));

        // Define the column names to reduce
        $columnNames = ['first_name'];

        // Call the reduce method
        $reducedData = ColumnReducer::reduce($reader, ...$columnNames);

        // Define the expected output
        $expectedOutput = [
            ['first_name' => 'Mieke'],
            ['first_name' => 'Gordie'],
        ];

        // Assert that the result matches the expected output
        $this->assertEquals($expectedOutput, $reducedData->getItems());
    }
}