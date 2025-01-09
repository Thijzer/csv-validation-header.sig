<?php

namespace Tests\Misery\Component\Statement;

use Misery\Component\Action\SetValueAction;
use Misery\Component\Statement\EqualsStatement;
use PHPUnit\Framework\TestCase;

class EqualsStatementTest extends TestCase
{
    public function test_it_should_set_a_value_with_an_equals_statement(): void
    {
        $action = new SetValueAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1234678',
        ];

        $statement = EqualsStatement::prepare($action);
        $statement
            ->when('brand', 'louis')
            ->then('brand', 'Louis Vuitton');

        $this->assertEquals([
            'brand' => 'Louis Vuitton',
            'description' => 'LV',
            'sku' => '1234678',
        ], $statement->apply($item));
    }

    public function test_it_should_not_set_a_value_with_a_equals_statement(): void
    {
        $action = new SetValueAction();

        $item = [
            'brand' => 'diesel',
            'description' => 'D',
            'sku' => '1234678',
        ];

        $statement = EqualsStatement::prepare($action);
        $statement
            ->when('brand', 'louis')
            ->then('brand', 'Louis Vuitton')
        ;

        $this->assertEquals([
            'brand' => 'diesel',
            'description' => 'D',
            'sku' => '1234678',
        ], $statement->apply($item));

        $statement
            ->when('brand', 'dies')
            ->then('brand', 'Louis Vuitton')
        ;

        $this->assertEquals([
            'brand' => 'diesel',
            'description' => 'D',
            'sku' => '1234678',
        ], $statement->apply($item));
    }

    public function test_it_should_not_set_a_value_with_a_multiple_statement(): void
    {
        $action = new SetValueAction();
        $statement = EqualsStatement::prepare($action);
        $statement
            ->when('brand', 'louis')
            ->then('brand', 'Louis Vuitton')
        ;
        $statement
            ->when('brand', 'mi')
            ->then('brand', 'Xiaomi')
        ;
        $statement
            ->when('brand', 'diesel')
            ->then('brand', 'Diesel inc.')
        ;

        $item = [
            'brand' => 'diesel',
            'description' => 'D',
            'sku' => '1234678',
        ];

        $this->assertEquals([
            'brand' => 'Diesel inc.',
            'description' => 'D',
            'sku' => '1234678',
        ], $statement->apply($item));

        $item = [
            'brand' => 'mi',
            'description' => 'D',
            'sku' => '1234678',
        ];

        $this->assertEquals([
            'brand' => 'Xiaomi',
            'description' => 'D',
            'sku' => '1234678',
        ], $statement->apply($item));
    }
}