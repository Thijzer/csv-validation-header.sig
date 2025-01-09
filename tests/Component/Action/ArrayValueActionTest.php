<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\ArrayValueAction;
use PHPUnit\Framework\TestCase;

class ArrayValueActionTest extends TestCase
{
    public function testApplyArrayPush()
    {
        $action = new ArrayValueAction();
        $item = [
            'field1' => [],
            'column_item1' => 'value1',
        ];

        $action->setOptions(['field' => 'field1', 'column_item' => 'column_item1', 'function' => 'array_push']);
        $result = $action->apply($item);

        $expectedResult = [
            'field1' => ['value1'],
            'column_item1' => 'value1',
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function testApplyArrayMerge()
    {
        $action = new ArrayValueAction();
        $item = [
            'field1' => ['value1'],
            'column_item1' => ['value2', 'value3'],
        ];

        $action->setOptions(['field' => 'field1', 'column_item' => 'column_item1', 'function' => 'array_merge']);
        $result = $action->apply($item);

        $expectedResult = [
            'field1' => ['value1', 'value2', 'value3'],
            'column_item1' => ['value2', 'value3'],
        ];

        $this->assertEquals($expectedResult, $result);
    }
}