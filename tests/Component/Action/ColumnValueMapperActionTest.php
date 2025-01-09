<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\ColumnValueMapperAction;
use PHPUnit\Framework\TestCase;

class ColumnValueMapperActionTest extends TestCase
{
    public function testApplyWithMatchingValues()
    {
        $item = ['column1' => 'value1', 'column2' => 'value2'];

        $action = new ColumnValueMapperAction();
        $action->setOptions([
            'list' => [
                'column1-value1' => 'new_value1',
                'column2-value2' => 'new_value2'
            ]
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['column1' => 'new_value1', 'column2' => 'new_value2'], $result);
    }

    public function testApplyWithNonMatchingValues()
    {
        $item = ['column1' => 'value1', 'column2' => 'value3'];

        $action = new ColumnValueMapperAction();
        $action->setOptions([
            'list' => [
                'column1-value1' => 'new_value1',
                'column2-value2' => 'new_value2'
            ]
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['column1' => 'new_value1', 'column2' => 'value3'], $result);
    }
}