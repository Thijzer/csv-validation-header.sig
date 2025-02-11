<?php

namespace Tests\Misery\Component\Action;
use Misery\Component\Action\ListMapperAction;
use PHPUnit\Framework\TestCase;

class ListMapperActionTest extends TestCase
{
    public function testApplyWithMatchingValue()
    {
        $item = ['field' => 'value1'];

        $action = new ListMapperAction();
        $action->setOptions([
            'field' => 'field',
            'list' => ['value1' => 'new_value1']
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field' => 'new_value1'], $result);
    }

    public function testApplyWithNonMatchingValue()
    {
        $item = ['field' => 'value2'];

        $action = new ListMapperAction();
        $action->setOptions([
            'field' => 'field',
            'list' => ['value1' => 'new_value1']
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field' => 'value2'], $result);
    }
}
