<?php

namespace Tests\Misery\Component\Compare;

use Misery\Component\Compare\ItemCompare;
use Misery\Component\Reader\ItemCollection;
use Misery\Component\Reader\ItemReader;
use PHPUnit\Framework\TestCase;

class CompareTest extends TestCase
{
    private $items = [
        [
            'id' => '1',
            'first_name' => 'Gordie',
        ],
        [
            'id' => '2',
            'first_name' => 'Frans',
        ],
    ];

    public function test_parse_csv_file(): void
    {
        $setA = new ItemCollection($this->items);
        $setB = clone $setA;
        $setB->set(1, [
            'id' => '2',
            'first_name' => 'Fransken',
        ]);

        $tool = new ItemCompare(
            $setA,
            $setB
        );

        $result = $tool->compare('id');

        $this->assertCount(1, $result['items'][ItemCompare::CHANGED]);
        $this->assertSame('Frans', $result['items'][ItemCompare::CHANGED][1]['changes']['first_name'][ItemCompare::BEFORE]);
        $this->assertSame('Fransken', $result['items'][ItemCompare::CHANGED][1]['changes']['first_name'][ItemCompare::AFTER]);
    }

    public function test_parse_csv_file_changes(): void
    {
        $setA = new ItemCollection($this->items);
        $setB = clone $setA;
        $setA->set(1, [
            'id' => '2',
            'first_name' => 'Fransken',
        ]);

        $tool = new ItemCompare(
            $setA,
            $setB
        );

        $result = $tool->compare('id');

        $this->assertCount(1, $result['items'][ItemCompare::CHANGED]);
        $this->assertSame('Fransken', $result['items'][ItemCompare::CHANGED][1]['changes']['first_name'][ItemCompare::BEFORE]);
        $this->assertSame('Frans', $result['items'][ItemCompare::CHANGED][1]['changes']['first_name'][ItemCompare::AFTER]);
    }

    public function test_parse_csv_file_change_empty_to_value(): void
    {
        $setA = new ItemCollection($this->items);
        $setB = clone $setA;
        $setA->set(1, [
            'id' => '2',
            'first_name' => '',
        ]);

        $tool = new ItemCompare(
            $setA,
            $setB
        );

        $result = $tool->compare('id');

        $this->assertCount(1, $result['items'][ItemCompare::CHANGED]);
        $this->assertSame('', $result['items'][ItemCompare::CHANGED][1]['changes']['first_name'][ItemCompare::BEFORE]);
        $this->assertSame('Frans', $result['items'][ItemCompare::CHANGED][1]['changes']['first_name'][ItemCompare::AFTER]);
    }

    public function test_parse_csv_file_change_null_to_value(): void
    {
        $setA = new ItemCollection($this->items);
        $setB = clone $setA;
        $setA->set(1, [
            'id' => '2',
            'first_name' => null,
        ]);

        $tool = new ItemCompare(
            $setA,
            $setB
        );

        $result = $tool->compare('id');

        $this->assertCount(1, $result['items'][ItemCompare::CHANGED]);
        $this->assertSame(null, $result['items'][ItemCompare::CHANGED][1]['changes']['first_name'][ItemCompare::BEFORE]);
        $this->assertSame('Frans', $result['items'][ItemCompare::CHANGED][1]['changes']['first_name'][ItemCompare::AFTER]);
    }

    public function test_parse_csv_file_change_value_to_empty(): void
    {
        $setA = new ItemCollection($this->items);
        $setB = clone $setA;
        $setB->set(1, [
            'id' => '2',
            'first_name' => '',
        ]);

        $tool = new ItemCompare(
            $setA,
            $setB
        );

        $result = $tool->compare('id');

        $this->assertCount(1, $result['items'][ItemCompare::CHANGED]);
        $this->assertSame('Frans', $result['items'][ItemCompare::CHANGED][1]['changes']['first_name'][ItemCompare::BEFORE]);
        $this->assertSame('', $result['items'][ItemCompare::CHANGED][1]['changes']['first_name'][ItemCompare::AFTER]);
    }

    public function test_parse_csv_file_change_value_to_null(): void
    {
        $setA = new ItemCollection($this->items);
        $setB = clone $setA;
        $setB->set(1, [
            'id' => '2',
            'first_name' => null,
        ]);

        $tool = new ItemCompare(
            $setA,
            $setB
        );

        $result = $tool->compare('id');

        $this->assertCount(1, $result['items'][ItemCompare::CHANGED]);
        $this->assertSame('Frans', $result['items'][ItemCompare::CHANGED][1]['changes']['first_name'][ItemCompare::BEFORE]);
        $this->assertSame(null, $result['items'][ItemCompare::CHANGED][1]['changes']['first_name'][ItemCompare::AFTER]);
    }

    public function test_parse_csv_remove_file(): void
    {
        $setA = new ItemCollection($this->items);
        $setB = new ItemCollection([
            [
                'id' => '1',
                'first_name' => 'Gordie',
            ],
        ]);

        $tool = new ItemCompare(
            $setA,
            $setB
        );

        $result = $tool->compare('id');

        $this->assertCount(1, $result['items'][ItemCompare::REMOVED]);
    }

    public function test_parse_csv_add_file(): void
    {
        $setA = new ItemCollection($this->items);
        $setB = clone $setA;
        $setB->set(2, [
            'id' => '3',
            'first_name' => 'Fransken',
        ]);

        $tool = new ItemCompare(
            $setA,
            $setB
        );

        $result = $tool->compare('id');

        $this->assertCount(1, $result['items'][ItemCompare::ADDED]);
    }
}