<?php

namespace Component\Statement;

use Misery\Component\Action\SetValueAction;
use Misery\Component\Statement\CharlenStatement;
use PHPUnit\Framework\TestCase;

class CharLenStatementTest extends TestCase
{
    public function test_it_should_not_apply_the_statement(): void
    {
        $action = new SetValueAction();

        $item = [
            'sku' => '012345',
            'type' => 'small',
        ];

        $context = [
            'condition' => 'greater_than',
            'char_len' => 6,
        ];

        $statement = CharlenStatement::prepare($action, $context);
        $statement
            ->when('sku')
            ->then('type', 'large');

        $this->assertEquals([
            'sku' => '012345',
            'type' => 'small',
        ], $statement->apply($item));
    }

    public function test_it_should_apply_the_statement(): void
    {
        $action = new SetValueAction();

        $item = [
            'sku' => '0123456',
            'type' => 'small',
        ];

        $context = [
            'condition' => 'greater_than',
            'char_len' => 6,
        ];

        $statement = CharlenStatement::prepare($action, $context);
        $statement
            ->when('sku')
            ->then('type', 'large');

        $this->assertEquals([
            'sku' => '0123456',
            'type' => 'large',
        ], $statement->apply($item));
    }
}