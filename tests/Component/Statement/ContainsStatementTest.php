<?php

namespace Tests\Misery\Component\Statement;

use Misery\Component\Action\SetValueAction;
use Misery\Component\Statement\ContainsStatement;
use PHPUnit\Framework\TestCase;

class ContainsStatementTest extends TestCase
{
    public function test_it_should_set_a_value_with_a_contains_statement(): void
    {
        $action = new SetValueAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1234678',
        ];

        $statement = ContainsStatement::prepare($action);
        $statement
            ->when('brand', 'louis')
            ->then('brand', 'Louis Vuitton');

        $this->assertEquals([
            'brand' => 'Louis Vuitton',
            'description' => 'LV',
            'sku' => '1234678',
        ], $statement->apply($item));

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => 'LV-1234-678',
        ];

        $statement
            ->when('sku', 'LV')
            ->then('brand', 'Louis Vuitton');

        $this->assertEquals([
            'brand' => 'Louis Vuitton',
            'description' => 'LV',
            'sku' => 'LV-1234-678',
        ], $statement->apply($item));
    }

    public function test_it_should_not_set_a_value_with_a_contains_statement(): void
    {
        $action = new SetValueAction();

        $item = [
            'brand' => 'diesel',
            'description' => 'D',
            'sku' => '1234678',
        ];

        $statement = ContainsStatement::prepare($action);
        $statement
            ->when('brand', 'louis')
            ->then('brand', 'Louis Vuitton')
        ;

        $this->assertEquals([
            'brand' => 'diesel',
            'description' => 'D',
            'sku' => '1234678',
        ], $statement->apply($item));
    }
}