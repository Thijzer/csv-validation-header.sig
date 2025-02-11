<?php

namespace Component\Action;

use Misery\Component\Action\FrameAction;
use PHPUnit\Framework\TestCase;

class FrameActionTest extends TestCase
{
    public function test_it_should_retain_fields(): void
    {
        $format = new FrameAction();
        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'fields' => ['brand', 'description'],
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'description' => 'LV',
        ], $format->apply($item));
    }

    public function test_it_should_not_retain_any_fields(): void
    {
        $format = new FrameAction();
        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'fields' => [
                'identifier'
            ],
        ]);

        $this->assertEquals(['identifier' => null], $format->apply($item));
    }

    public function test_it_should_do_a_retain_with_weird_types(): void
    {
        $format = new FrameAction();
        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
            0 => [false],
        ];

        $format->setOptions([
            'fields' => ['brand', 'description'],
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'description' => 'LV',
        ], $format->apply($item));
    }

    public function test_labels_with_flat_data(): void
    {
        $format = new FrameAction();
        $item = [
            'sku' => 'sku-thomas',
            'labels-nl_NL' => 'label NL',
            'labels-fr_FR' => 'label FR',
        ];

        $format->setOptions([
            'fields' => ['sku', 'labels-nl_NL'],
        ]);

        $this->assertEquals([
            'sku' => 'sku-thomas',
            'labels-nl_NL' => 'label NL',
        ], $format->apply($item));
    }

    public function test_labels_with_flat_data_and_null_values(): void
    {
        $format = new FrameAction();
        $item = [
            'sku' => 'sku-thomas',
            'labels-nl_NL' => null,
            'labels-fr_FR' => null,
        ];

        $format->setOptions([
            'fields' => ['sku', 'labels-nl_NL'],
        ]);

        $this->assertEquals([
            'sku' => 'sku-thomas',
            'labels-nl_NL' => null,
        ], $format->apply($item));
    }

    public function test_labels_with_nested_data(): void
    {
        $format = new FrameAction();
        $item = [
            'sku' => 'sku-thomas',
            'labels' => [
                'nl_NL' => 'label NL',
                'fr_FR' => 'label FR',
            ]
        ];

        $format->setOptions([
            'fields' => ['sku', 'labels'],
        ]);

        $this->assertEquals([
            'sku' => 'sku-thomas',
            'labels' => [
                'nl_NL' => 'label NL',
                'fr_FR' => 'label FR',
            ],
        ], $format->apply($item));
    }

    public function test_with_multi_dimensional_data(): void
    {
        $format = new FrameAction();
        $item = [
            'sku' => 'sku',
            'labels' => [
                'nl_NL' => 'label NL',
                'fr_FR' => 'label FR',
            ]
        ];

        $format->setOptions([
            'fields' => ['sku', 'labels'],
        ]);

        $this->assertEquals([
            'sku' => 'sku',
            'labels' => [
                'nl_NL' => 'label NL',
                'fr_FR' => 'label FR',
            ],
        ], $format->apply($item));
    }

    public function test_the_fields_order(): void
    {
        $format = new FrameAction();
        $item = [
            'sku' => '1',
            'description' => 'LV',
            'status' => 'active',
            'parent' => 'null',
        ];

        $format->setOptions([
            'fields' => ['sku', 'parent', 'status', 'description'],
        ]);

        $this->assertEquals([
            'sku' => '1',
            'status' => 'active',
            'parent' => 'null',
            'description' => 'LV',
        ], $format->apply($item));
    }

    public function test_the_fields_with_default_values(): void
    {
        $format = new FrameAction();
        $item = [
            'sku' => '1',
            'description' => 'LV',
            'parent' => null,
        ];

        $format->setOptions([
            'fields' => [
                'sku' => '',
                'parent' => '',
                'status' => 'active',
                'description' => '',
            ],
        ]);

        $this->assertEquals([
            'sku' => '1',
            'parent' => null,
            'status' => 'active',
            'description' => 'LV',
        ], $format->apply($item));
    }

    public function test_the_fields_without_default_values(): void
    {
        $format = new FrameAction();
        $item = [
            'sku' => '1',
            'description' => 'LV',
            'parent' => null,
        ];

        $format->setOptions([
            'fields' => [
                'sku',
                'parent',
                'status',
                'description',
            ],
        ]);

        $this->assertEquals([
            'sku' => '1',
            'parent' => null,
            'status' => null,
            'description' => 'LV',
        ], $format->apply($item));
    }
}