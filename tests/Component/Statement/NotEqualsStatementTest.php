<?php

namespace Component\Statement;

use Misery\Component\Action\SetValueAction;
use Misery\Component\Statement\NotEqualStatement;
use PHPUnit\Framework\TestCase;

class NotEqualsStatementTest extends TestCase
{
    public function test_it_should_set_a_value_with_not_equals_statement(): void
    {
        $action = new SetValueAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1234678',
        ];

        $statement = NotEqualStatement::prepare($action);
        $statement
            ->when('brand', 'louisa')
            ->then('brand', 'Louis Vuitton');

        $this->assertEquals([
            'brand' => 'Louis Vuitton',
            'description' => 'LV',
            'sku' => '1234678',
        ], $statement->apply($item));
    }

    public function test_it_should_not_set_a_value_with_not_equals_statement(): void
    {
        $action = new SetValueAction();

        $item = [
            'brand' => 'diesel',
            'description' => 'D',
            'sku' => '1234678',
        ];

        $statement = NotEqualStatement::prepare($action);
        $statement
            ->when('brand', 'diesel')
            ->then('brand', 'Louis Vuitton')
        ;

        $this->assertEquals([
            'brand' => 'diesel',
            'description' => 'D',
            'sku' => '1234678',
        ], $statement->apply($item));
    }
}