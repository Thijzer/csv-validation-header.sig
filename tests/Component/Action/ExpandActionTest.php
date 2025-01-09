<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\CopyAction;
use Misery\Component\Action\ExpandAction;
use Misery\Component\Action\RenameAction;
use PHPUnit\Framework\TestCase;

class ExpandActionTest extends TestCase
{
    public function test_it_should_do_an_expand_action_with_expansion(): void
    {
        $format = new ExpandAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'set' => [
                'id' => '',
                'brand' => '',
                'description' => '',
                'sku' => '',
            ]
        ]);

        $this->assertEquals([
            'description' => 'LV',
            'sku' => '1',
            'id' => '',
            'brand' => 'louis',
        ], $format->apply($item));
    }

    public function test_it_should_do_an_expand_action_with_expansion_and_values(): void
    {
        $format = new ExpandAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'set' => [
                'id' => 'louis_1',
                'brand' => '',
                'description' => '',
                'sku' => '',
            ]
        ]);

        $this->assertEquals([
            'description' => 'LV',
            'sku' => '1',
            'id' => 'louis_1',
            'brand' => 'louis',
        ], $format->apply($item));
    }

    public function test_it_should_do_an_expand_action_without_expansion(): void
    {
        $format = new ExpandAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'set' => [
                'description' => '',
                'sku' => '',
            ]
        ]);

        $this->assertEquals([
            'description' => 'LV',
            'sku' => '1',
            'brand' => 'louis',
        ], $format->apply($item));
    }

    public function test_it_should_do_an_expand_action_set_column_order(): void
    {
        $format = new ExpandAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'set' => [
                'brand' => null,
                'description' => null,
                'sku' => null,
            ]
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ], $format->apply($item));
    }

    public function test_it_should_do_an_expand_action_dont_override_values(): void
    {
        $format = new ExpandAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'set' => [
                'brand' => 'jake',
                'description' => null,
                'sku' => null,
            ]
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ], $format->apply($item));
    }

    public function test_it_should_expand_on_null_values(): void
    {
        $format = new ExpandAction();

        $item = [
            'brand' => 'louis',
            'sku' => '1',
        ];

        $format->setOptions([
            'set' => [
                'brand' => null,
                'description' => null,
                'sku' => null,
            ]
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'description' => null,
            'sku' => '1',
        ], $format->apply($item));
    }

    public function test_it_should_expand_on_list_values(): void
    {
        $format = new ExpandAction();

        $item = [
            'brand' => 'louis',
            'sku' => '1',
        ];

        $format->setOptions([
            'list' => [
                'brand' => 'a',
                'description' => 'b',
                'sku' => 'c',
            ]
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'description' => 'b',
            'sku' => '1',
        ], $format->apply($item));
    }
}