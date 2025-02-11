<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\SetValueAction;
use Misery\Component\Converter\Matcher;
use PHPUnit\Framework\TestCase;

class SetValueActionTest extends TestCase
{
    public function test_it_should_set_a_value_action(): void
    {
        $format = new SetValueAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
            'enabled' => '0',
        ];

        $format->setOptions([
            'key' => 'enabled',
            'value' => '1',
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
            'enabled' => '1',
        ], $format->apply($item));
    }

    public function test_it_should_not_set_a_new_value_action(): void
    {
        $format = new SetValueAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'key' => 'published',
            'value' => '1',
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ], $format->apply($item));
    }

    public function test_it_should_set_a_null_value_action(): void
    {
        $format = new SetValueAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
            'published' => '1',
        ];

        $format->setOptions([
            'field' => 'published',
            'value' => null,
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
            'published' => null,
        ], $format->apply($item));
    }

    public function test_it_should_set_fields_std_data()
    {
        $format = new SetValueAction();

        $item = [
            'values|street' => [
                'matcher' => Matcher::create('street'),
                'scope' => null,
                'locale' => null,
                'data' => '123 Main Street',
            ],
        ];

        $format->setOptions([
            'field' => 'street',
            'value' => null
        ]);

        $this->assertEquals([
            'values|street' => [
                'matcher' => Matcher::create('street'),
                'scope' => null,
                'locale' => null,
                'data' => null,
            ],
        ], $format->apply($item));

        $format->setOptions([
            'field' => 'street',
            'value' => 'unknown',
        ]);

        $this->assertEquals([
            'values|street' => [
                'matcher' => Matcher::create('street'),
                'scope' => null,
                'locale' => null,
                'data' => 'unknown',
            ],
        ], $format->apply($item));
    }

    public function test_it_should_not_set_fields_std_data()
    {
        $format = new SetValueAction();

        $item = [
            'values|street' => [
                'matcher' => Matcher::create('street'),
                'scope' => null,
                'locale' => null,
                'data' => '123 Main Street',
            ],
        ];

        $format->setOptions([
            'field' => 'zone',
            'value' => 'urban'
        ]);

        $this->assertEquals([
            'values|street' => [
                'matcher' => Matcher::create('street'),
                'scope' => null,
                'locale' => null,
                'data' => '123 Main Street',
            ],
        ], $format->apply($item));
    }
}