<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\CopyAction;
use Misery\Component\Action\RenameAction;
use PHPUnit\Framework\TestCase;

class RenameActionTest extends TestCase
{
    public function test_it_should_do_a_rename_action(): void
    {
        $format = new RenameAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'from' => 'brand',
            'to' => 'merk',
        ]);

        $this->assertEquals([
            'merk' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ], $format->apply($item));
    }

    public function test_it_should_do_a_rename_with_fields_action(): void
    {
        $format = new RenameAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'fields' => [
                'brand' => 'merk',
                'description' => 'omschrijving',
            ],
        ]);

        $this->assertEquals([
            'merk' => 'louis',
            'omschrijving' => 'LV',
            'sku' => '1',
        ], $format->apply($item));
    }

    public function test_it_should_do_a_rename_with_suffix_action(): void
    {
        $format = new RenameAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'suffix' => '_warehouse',
            'fields' => ['brand', 'description'],
        ]);

        // the order is broken
        $this->assertEquals([
            'sku' => '1',
            'brand_warehouse' => 'louis',
            'description_warehouse' => 'LV',
        ], $format->apply($item));
    }
}