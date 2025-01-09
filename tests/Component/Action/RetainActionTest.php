<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\RetainAction;
use PHPUnit\Framework\TestCase;

class RetainActionTest extends TestCase
{
    public function test_it_should_retain_action_with_keys(): void
    {
        $format = new RetainAction();
        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'keys' => ['brand', 'description'],
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'description' => 'LV',
        ], $format->apply($item));
    }

    public function test_it_should_not_retain_action_with_keys(): void
    {
        $format = new RetainAction();
        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'keys' => [],
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ], $format->apply($item));
    }


    public function test_it_should_do_a_retain_action_with_bad_keys(): void
    {
        $format = new RetainAction();
        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
            0 => [false],
        ];

        $format->setOptions([
            'keys' => ['brand', 'description', 0, false, true, '', -1, 'the-unknown'],
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'description' => 'LV',
            0 => [false],
        ], $format->apply($item));
    }

    public function test_labels_with_flat_data(): void
    {
        $format = new RetainAction();
        $item = [
            'sku' => 'sku-thomas',
            'labels-nl_NL' => 'label NL',
            'labels-fr_FR' => 'label FR',
        ];

        $format->setOptions([
            'keys' => ['sku', 'labels-nl_NL'],
        ]);

        $this->assertEquals([
            'sku' => 'sku-thomas',
            'labels-nl_NL' => 'label NL',
        ], $format->apply($item));
    }

    public function test_labels_with_flat_data_and_null_values(): void
    {
        $format = new RetainAction();
        $item = [
            'sku' => 'sku-thomas',
            'labels-nl_NL' => null,
            'labels-fr_FR' => null,
        ];

        $format->setOptions([
            'keys' => ['sku', 'labels-nl_NL'],
            'mode' => 'single',
        ]);

        $this->assertEquals([
            'sku' => 'sku-thomas',
            'labels-nl_NL' => null,
        ], $format->apply($item));
    }

    public function test_labels_with_nested_data(): void
    {
        $format = new RetainAction();
        $item = [
            'sku' => 'sku-thomas',
            'labels' => [
                'nl_NL' => 'label NL',
                'fr_FR' => 'label FR',
            ]
        ];

        $format->setOptions([
            'keys' => ['sku', 'labels-fr_FR'],
        ]);

        $this->assertEquals([
            'sku' => 'sku-thomas',
            'labels-fr_FR' => 'label FR',
        ], $format->apply($item));
    }
}