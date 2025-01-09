<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\CopyAction;
use PHPUnit\Framework\TestCase;

class CopyActionTest extends TestCase
{
    public function test_it_should_do_a_copy_action(): void
    {
        $format = new CopyAction();

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
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
            'merk' => 'louis',
        ], $format->apply($item));
    }
}