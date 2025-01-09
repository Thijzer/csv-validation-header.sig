<?php

namespace Tests\Misery\Component\Statement;

use Misery\Component\Action\SetValueAction;
use Misery\Component\Statement\InListStatement;
use PHPUnit\Framework\TestCase;

class InListStatementTest extends TestCase
{
    public function test_it_should_set_a_value_with_an_in_list_statement(): void
    {
        $action = new SetValueAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1234678',
        ];

        $statement = InListStatement::prepare($action, [
            'list' => ['louis', 'jack', 'jones']
        ]);
        $statement
            ->when('brand')
            ->then('brand', 'Louis Vuitton');

        $this->assertEquals([
            'brand' => 'Louis Vuitton',
            'description' => 'LV',
            'sku' => '1234678',
        ], $statement->apply($item));
    }

    public function test_it_should_set_a_value_with_an_in_list_of_array_statement(): void
    {
        $action = new SetValueAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1234678',
        ];

        $statement = InListStatement::prepare($action, [
            'list' => ['louis' => 'a', 'jack' => 'b', 'jones' => 'c']
        ]);
        $statement
            ->when('brand')
            ->then('brand', 'Louis Vuitton');

        $this->assertEquals([
            'brand' => 'Louis Vuitton',
            'description' => 'LV',
            'sku' => '1234678',
        ], $statement->apply($item));
    }
}