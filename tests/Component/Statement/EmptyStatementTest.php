<?php

namespace Tests\Misery\Component\Statement;

use Misery\Component\Action\SetValueAction;
use Misery\Component\Statement\EmptyStatement;
use PHPUnit\Framework\TestCase;

class EmptyStatementTest extends TestCase
{
    public function test_it_should_set_a_value_with_an_empty_statement(): void
    {
        $action = new SetValueAction();

        $item = [
            'brand' => '',
            'description' => 'LV',
            'sku' => '1234678',
        ];

        $statement = EmptyStatement::prepare($action);
        $statement
            ->when('brand')
            ->then('brand', 'Louis Vuitton');

        $this->assertEquals([
            'brand' => 'Louis Vuitton',
            'description' => 'LV',
            'sku' => '1234678',
        ], $statement->apply($item));

        $item2 = [
            'brand' => null,
            'description' => 'LV',
            'sku' => '1234678',
        ];

        $this->assertEquals([
            'brand' => 'Louis Vuitton',
            'description' => 'LV',
            'sku' => '1234678',
        ], $statement->apply($item2));
    }
}