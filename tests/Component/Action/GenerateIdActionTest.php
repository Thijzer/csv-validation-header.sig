<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\GenerateIdAction;
use PHPUnit\Framework\TestCase;

class GenerateIdActionTest extends TestCase
{
    public function testApplyAutoIncrement()
    {
        $item = ['some_field' => 'value'];

        $action = new GenerateIdAction();
        $action->setOption('start_id', 1);
        $action->setOption('field', 'id');
        $result = $action->apply($item);

        $this->assertEquals(['some_field' => 'value', 'id' => 1], $result);

        // Apply the action again to test auto-increment
        $result = $action->apply($item);
        $this->assertEquals(['some_field' => 'value', 'id' => 2], $result);
    }

    public function testApplyAutoIncrementWithStartId()
    {
        $item = ['some_field' => 'value'];

        $action = new GenerateIdAction();
        $action->setOption('start_id', 10000);
        $action->setOption('field', 'id');
        $result = $action->apply($item);

        $this->assertEquals(['some_field' => 'value', 'id' => 10000], $result);

        // Apply the action again to test auto-increment
        $result = $action->apply($item);
        $this->assertEquals(['some_field' => 'value', 'id' => 10001], $result);
    }

    public function testApplyAutoSequenceWithFormat()
    {
        $item = ['attribute' => 'color', 'field2' => 'value2'];

        $action = new GenerateIdAction();
        $action->setOptions([
            'format' => '%attribute%_%auto-sequence%',
            'format_fields' => ['attribute'],
            'field' => 'sequence',
        ]);
        $result = $action->apply($item);

        $this->assertEquals(['attribute' => 'color', 'field2' => 'value2', 'sequence' => 'color_1'], $result);

        // Apply the action again to test auto-sequence
        $result = $action->apply($item);
        $this->assertEquals(['attribute' => 'color', 'field2' => 'value2', 'sequence' => 'color_2'], $result);

        // Apply the action again to test auto-sequence
        $result = $action->apply(['attribute' => 'brand', 'field2' => 'value2']);
        $this->assertEquals(['attribute' => 'brand', 'field2' => 'value2', 'sequence' => 'brand_1'], $result);
    }
}
