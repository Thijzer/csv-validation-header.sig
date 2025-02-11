<?php

namespace Component\Statement;

use Misery\Component\Action\SetValueAction;
use Misery\Component\Statement\NotEmptyStatement;
use PHPUnit\Framework\TestCase;

class NotEmptyStatementTest extends TestCase
{
    public function test_it_should_set_a_value_with_no_empty_statement(): void
    {
        $action = new SetValueAction();

        $item = [
            'brand' => 'JB',
            'description' => 'LV',
            'sku' => '1234678',
        ];

        $statement = NotEmptyStatement::prepare($action);
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
            'brand' => null,
            'description' => 'LV',
            'sku' => '1234678',
        ], $statement->apply($item2));
    }
}