<?php

namespace Component\Statement;

use Misery\Component\Action\SetValueAction;
use Misery\Component\Statement\GreaterThanOrEqualStatement;
use PHPUnit\Framework\TestCase;

class GreaterThenOrEqualStatementTest extends TestCase
{
    public function test_it_should_apply_the_numeric_statement(): void
    {
        $action = new SetValueAction();

        $item = [
            'type' => 'string',
            'power_number' => '10',
        ];

        $statement = GreaterThanOrEqualStatement::prepare($action);
        $statement
            ->when('power_number', '10')
            ->then('type', 'high');

        $this->assertEquals([
            'type' => 'high',
            'power_number' => '10',
        ], $statement->apply($item));


        $this->assertEquals([
            'type' => 'string',
            'power_number' => '9',
        ], $statement->apply(array_merge($item, ['power_number' => '9'])));

        $this->assertEquals([
            'type' => 'high',
            'power_number' => '100',
        ], $statement->apply(array_merge($item, ['power_number' => '100'])));
    }

}