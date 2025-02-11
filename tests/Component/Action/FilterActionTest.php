<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\FilterAction;
use PHPUnit\Framework\TestCase;

class FilterActionTest extends TestCase
{
    public function test_it_should_not_filter_values_action(): void
    {
        $format = new FilterAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'key' => 'brand',
            'match' => 'lou',
        ]);

        $this->assertEquals(
            $item
        , $format->apply($item));
    }

    public function test_it_should_filter_values_action(): void
    {
        $format = new FilterAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'key' => 'brand',
            'match' => 'chris',
        ]);

        $this->assertEquals(
            [
                'brand' => null,
                'description' => 'LV',
                'sku' => '1',
            ],
            $format->apply($item)
        );
    }

    public function test_it_should_filter_values_in_list_action(): void
    {
        $format = new FilterAction();

        $item = [
            'categories' => ['A-a', 'A-b', 'C-a', 'C-b'],
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'key' => 'categories',
            'match' => 'A-',
        ]);

        $this->assertSame(
            [
                'categories' =>  ['A-a', 'A-b'],
                'description' => 'LV',
                'sku' => '1',
            ],
            $format->apply($item)
        );
    }

    public function test_it_should_filter_values_in_list_case_sensitive_action(): void
    {
        $format = new FilterAction();

        $item = [
            'categories' => ['A-a', 'A-b', 'a-A', 'a-B'],
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'key' => 'categories',
            'case-sensitive' => true,
            'match' => 'A-',
        ]);

        $this->assertSame(
            [
                'categories' =>  ['A-a', 'A-b'],
                'description' => 'LV',
                'sku' => '1',
            ],
            $format->apply($item)
        );
    }

    public function test_it_should_filter_values_in_list_case_insensitive_action(): void
    {
        $format = new FilterAction();

        $item = [
            'categories' => ['A-a', 'A-b', 'a-A', 'a-B', 'c-d'],
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'key' => 'categories',
            'case-sensitive' => false, # default false
            'match' => 'A-',
        ]);

        $this->assertSame(
            [
                'categories' =>  ['A-a', 'A-b', 'a-A', 'a-B'],
                'description' => 'LV',
                'sku' => '1',
            ],
            $format->apply($item)
        );
    }

    public function test_it_should_filter_equals_case_insensitive(): void
    {
        $format = new FilterAction();

        $item = [
            'categories' => ['A-a', 'A-b', 'a-A', 'a-B', 'c-d'],
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'key' => 'categories',
            'case-sensitive' => false, # default false
            'equals' => 'A-a',
        ]);

        $this->assertSame(
            [
                'categories' =>  [1 => 'A-b', 3 => 'a-B', 4 => 'c-d'],
                'description' => 'LV',
                'sku' => '1',
            ],
            $format->apply($item)
        );
    }

    public function test_it_should_filter_equals_case_sensitive(): void
    {
        $format = new FilterAction();

        $item = [
            'categories' => ['A-a', 'A-b', 'a-A', 'a-B', 'c-d'],
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'key' => 'categories',
            'case-sensitive' => true, # default false
            'equals' => 'A-a',
        ]);

        $this->assertSame(
            [
                'categories' =>  [1 => 'A-b', 2 => 'a-A', 3 => 'a-B', 4 => 'c-d'],
                'description' => 'LV',
                'sku' => '1',
            ],
            $format->apply($item)
        );
    }
}