<?php

namespace Tests\Misery\Component\Compare;

use Misery\Component\Reader\ItemCollection;
use Misery\Component\Reader\ItemReader;
use Misery\Component\Item\Builder\ReferenceBuilder;
use PHPUnit\Framework\TestCase;

class ReferenceBuilderTest extends TestCase
{
    private $items = [
        [
            'code' => 'CODE',
        ],
        [
            'code' => 'HALLO',
        ],
        [
            'code' => 'BLAbla',
        ],
    ];

    public function test_parse_csv_file(): void
    {
        $itemCollection = new ItemCollection($this->items);

        $items = ReferenceBuilder::build(new ItemReader($itemCollection), 'code');

        $this->assertEquals($items, [
            'code' => [
                'CODE',
                'HALLO',
                'BLAbla',
            ]
        ]);
    }
}