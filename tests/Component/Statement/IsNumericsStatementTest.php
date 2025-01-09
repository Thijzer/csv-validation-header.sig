<?php

namespace Component\Statement;

use Misery\Component\Action\SetValueAction;
use Misery\Component\Statement\IsNumericsStatement;
use Misery\Component\Statement\NotEmptyStatement;
use PHPUnit\Framework\TestCase;

class IsNumericsStatementTest extends TestCase
{
    public function test_it_should_apply_the_numeric_statement(): void
    {
        $action = new SetValueAction();

        $item = [
            'type' => 'string',
            'sku' => '1234678',
        ];

        $statement = IsNumericsStatement::prepare($action);
        $statement
            ->when('sku')
            ->then('type', 'numeric');

        $this->assertEquals([
            'type' => 'numeric',
            'sku' => '1234678',
        ], $statement->apply($item));
    }

    public function test_it_should_not_apply_the_numeric_statement(): void
    {
        $action = new SetValueAction();

        $item = [
            'type' => 'string',
            'sku' => 'A1234678',
        ];

        $statement = IsNumericsStatement::prepare($action);
        $statement
            ->when('sku')
            ->then('type', 'numeric');

        $this->assertEquals([
            'type' => 'string',
            'sku' => 'A1234678',
        ], $statement->apply($item));
    }
}