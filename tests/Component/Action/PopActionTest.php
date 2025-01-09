<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\PopAction;
use PHPUnit\Framework\TestCase;

class PopActionTest extends TestCase
{
    public function testApply()
    {
        $item = ['field' => 'value1,value2,value3'];

        $action = new PopAction();
        $action->setOptions([
            'field' => 'field',
            'separator' => ','
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field' => 'value3'], $result);
    }

    public function testApplyWithEmptyField()
    {
        $item = [];

        $action = new PopAction();
        $action->setOptions([
            'field' => 'field',
            'separator' => ','
        ]);

        $result = $action->apply($item);

        $this->assertEquals([], $result);
    }

    public function testApplyWithEmptyValue()
    {
        $item = ['field' => ''];

        $action = new PopAction();
        $action->setOptions([
            'field' => 'field',
            'separator' => ','
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field' => ''], $result);
    }
}