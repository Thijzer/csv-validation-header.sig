<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\CalculateAction;
use PHPUnit\Framework\TestCase;

class CalculateActionTest extends TestCase
{
    public function testApplyWithAddOperator()
    {
        $item = ['field1' => 5, 'field2' => 10];

        $action = new CalculateAction();
        $action->setOptions([
            'fields' => ['field1', 'field2'],
            'operator' => 'ADD',
            'result' => 'sum'
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field1' => 5, 'field2' => 10, 'sum' => 15], $result);
    }

    public function testApplyWithMultiplyOperator()
    {
        $item = ['field1' => 5, 'field2' => 10];

        $action = new CalculateAction();
        $action->setOptions([
            'fields' => ['field1', 'field2'],
            'operator' => 'MULTIPLY',
            'result' => 'product'
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field1' => 5, 'field2' => 10, 'product' => 50], $result);
    }

    public function testApplyWithDivideOperator()
    {
        $item = ['field1' => 10, 'field2' => 2];

        $action = new CalculateAction();
        $action->setOptions([
            'fields' => ['field1', 'field2'],
            'operator' => 'DIVIDE',
            'result' => 'quotient'
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field1' => 10, 'field2' => 2, 'quotient' => 5], $result);
    }

    public function testApplyWithSubtractOperator()
    {
        $item = ['field1' => 20, 'field2' => 5];

        $action = new CalculateAction();
        $action->setOptions([
            'fields' => ['field1', 'field2'],
            'operator' => 'SUBTRACT',
            'result' => 'difference'
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field1' => 20, 'field2' => 5, 'difference' => 15], $result);
    }

    public function testApplyWithInvalidFields()
    {
        $item = ['field1' => 'text', 'field2' => 'invalid'];

        $action = new CalculateAction();
        $action->setOptions([
            'fields' => ['field1', 'field2'],
            'operator' => 'ADD',
            'result' => 'sum'
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field1' => 'text', 'field2' => 'invalid'], $result);
    }

    public function testApplyWithStringFieldsAndNegativeResult()
    {
        $item = ['INNERBOX_QUANTITY_CALCULATION' => '1', 'GIFTBOX_QUANTITY_CALCULATION' => '33'];

        $action = new CalculateAction();
        $action->setOptions([
            'fields' => ['INNERBOX_QUANTITY_CALCULATION', 'GIFTBOX_QUANTITY_CALCULATION'],
            'operator' => 'SUBTRACT',
            'result' => 'INNERBOX=GIFTBOX_CALCULATION'
        ]);

        $result = $action->apply($item);

        $item['INNERBOX=GIFTBOX_CALCULATION'] = '-32';
        $this->assertEquals($item, $result);
    }
}