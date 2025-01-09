<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\EmptyAction;
use PHPUnit\Framework\TestCase;

class EmptyActionTest extends TestCase
{
    public function testApplyWithKeyExists()
    {
        $item = ['sku' => '123', 'values' => [
            'nn_test' => [
                [
                    'locale' => null,
                    'scope' => null,
                    'data' => 'AB',
                ],
            ]
        ]];

        $action = new EmptyAction();
        $action->setOptions([
            'field' => 'values',
            'list' => ['test'],
            'prefix' =>  'nn_',
        ]);

        $this->assertEquals(['sku' => '123', 'values' => [
            'nn_test' => [
                [
                    'locale' => null,
                    'scope' => null,
                    'data' => null,
                ],
        ]]], $action->apply($item));

        $action = new EmptyAction();
        $action->setOptions([
            'field' => 'values',
            'list' => ['nn_test'],
        ]);

        $this->assertEquals(['sku' => '123', 'values' => [
        'nn_test' => [
            [
                'locale' => null,
                'scope' => null,
                'data' => null,
            ],
        ]]], $action->apply($item));
    }
}