<?php

namespace Component\Action;

use Misery\Component\Action\FilterFieldAction;
use PHPUnit\Framework\TestCase;

class FilterFieldActionTest extends TestCase
{
    public function test_it_should_not_filter_fields_action(): void
    {
        $format = new FilterFieldAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'fields' => ['brands'],
            'reverse' => true,
        ]);

        $this->assertEquals(
            $item
        , $format->apply($item));
    }

    public function test_it_should_filter_fields_action(): void
    {
        $format = new FilterFieldAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'fields' => ['brand'],
            'reverse' => true,
        ]);

        $this->assertEquals(
            [
                'description' => 'LV',
                'sku' => '1',
            ],
            $format->apply($item)
        );
    }
    public function test_it_should_filter_matching_contains_fields_action(): void
    {
        $format = new FilterFieldAction();

        $item = [
            'values|section-1' => 'A',
            'values|section-2' => 'B',
            'values|section-3' => 'C',
            'values|descriptions' => 'D',
        ];

        # Contains
        $format->setOptions([
            'contains' => 'values|section-',
        ]);

        $this->assertSame(
            [
                'values|section-1' => 'A',
                'values|section-2' => 'B',
                'values|section-3' => 'C',
            ],
            $format->apply($item)
        );
    }

    public function test_it_should_filter_matching_start_with_fields_action(): void
    {
        $format = new FilterFieldAction();

        $item = [
            'values|section-1' => 'A',
            'values|section-2' => 'B',
            'values|section-3' => 'C',
            'values|descriptions' => 'D',
        ];

        $format->setOptions([
            'starts_with' => 'values|section',
        ]);

        $this->assertSame(
            [
                'values|section-1' => 'A',
                'values|section-2' => 'B',
                'values|section-3' => 'C',
            ],
            $format->apply($item)
        );

        $format = new FilterFieldAction();

        $format->setOptions([
            'starts_with' => 'values|',
        ]);

        $this->assertSame(
            $item,
            $format->apply($item)
        );
    }

    public function test_it_should_filter_matching_ends_with_fields_action(): void
    {
        $format = new FilterFieldAction();

        $item = [
            'values|section-1' => 'A',
            'values|section-2' => 'B',
            'values|section-3' => 'C',
            'values|descriptions' => 'D',
        ];

        # Ends with
        $format->setOptions([
            'ends_with' => 'ions',
        ]);

        $this->assertSame(
            [
                'values|descriptions' => 'D',
            ],
            $format->apply($item)
        );
    }

    public function test_it_should_clear_filtered_values_action(): void
    {
        $format = new FilterFieldAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'fields' => ['brand'],
            'clear_value' => true,
        ]);

        $this->assertEquals([
            'brand' => null,
            'description' => 'LV',
            'sku' => '1',
        ], $format->apply($item));

        $format = new FilterFieldAction();

        $format->setOptions([
            'starts_with' => 'bran',
            'clear_value' => true,
        ]);

        $this->assertEquals([
            'brand' => null,
            'description' => 'LV',
            'sku' => '1',
        ], $format->apply($item));
    }
}