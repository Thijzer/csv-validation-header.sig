<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\CombineAction;
use PHPUnit\Framework\TestCase;

class CombineActionTest extends TestCase
{
    public function test_it_should_combine_item_values(): void
    {
        $format = new CombineAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'keys' => ['brand', 'description'],
            'header' => 'brand-description',
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
            'brand-description' => 'louis LV',
        ], $format->apply($item));
    }
}