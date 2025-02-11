<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\RepositionKeysAction;
use PHPUnit\Framework\TestCase;

class RepositionKeysActionTest extends TestCase
{
    public function testApplyWithSpecificKeys()
    {
        $item = ['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3'];

        $action = new RepositionKeysAction();
        $action->setOptions([
            'from' => ['key1', 'key3'],
            'to' => 'newRoot'
        ]);

        $result = $action->apply($item);

        $expectedResult = ['key2' => 'value2', 'newRoot' => ['key1' => 'value1', 'key3' => 'value3']];
        $this->assertEquals($expectedResult, $result);
    }

    public function testApplyWithAllKeys()
    {
        $item = ['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3'];

        $action = new RepositionKeysAction();
        $action->setOptions([
            'from' => 'all',
            'to' => 'newRoot'
        ]);

        $result = $action->apply($item);

        $expectedResult = ['newRoot' => ['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3']];
        $this->assertEquals($expectedResult, $result);
    }

    public function testApplyWithNoKeys()
    {
        $item = ['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3'];

        $action = new RepositionKeysAction();
        $action->setOptions([
            'from' => ['nonExistentKey'],
            'to' => 'newRoot'
        ]);

        $result = $action->apply($item);

        $this->assertEquals($item, $result);
    }
}
