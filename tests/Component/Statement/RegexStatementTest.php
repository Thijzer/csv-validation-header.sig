<?php

namespace Tests\Misery\Component\Statement;

use Misery\Component\Action\SetValueAction;
use Misery\Component\Statement\RegexStatement;
use PHPUnit\Framework\TestCase;

class RegexStatementTest extends TestCase
{
    public function test_it_should_set_a_value_with_a_regex_statement(): void
    {
        $action = new SetValueAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => 'C123',
        ];

        $statement = RegexStatement::prepare($action);
        $statement
            ->when('sku', '/^[a-z]{1,3}[0-9]{1,3}/i')
            ->then('brand', 'Louis Vuitton');

        $this->assertEquals([
            'brand' => 'Louis Vuitton',
            'description' => 'LV',
            'sku' => 'C123',
        ], $statement->apply($item));
    }

    public function test_it_should_not_set_a_value_with_a_regex_statement(): void
    {
        $action = new SetValueAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => 'ABCD12345',
        ];

        $statement = RegexStatement::prepare($action);
        $statement
            ->when('sku', '/^[a-z]{1,3}[0-9]{1,3}/i')
            ->then('brand', 'Louis Vuitton');

        $this->assertEquals([
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => 'ABCD12345',
        ], $statement->apply($item));
    }
}