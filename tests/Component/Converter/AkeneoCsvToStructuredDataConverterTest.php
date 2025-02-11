<?php

namespace Tests\Misery\Component\Converter;

use Misery\Component\Converter\AkeneoCsvHeaderContext;
use Misery\Component\Converter\AkeneoCsvToStructuredDataConverter;
use PHPUnit\Framework\TestCase;

class AkeneoCsvToStructuredDataConverterTest extends TestCase
{
    private $items = [
        [
            'sku' => '13445',
            'code-nl_BE-ecom' => 'value',
            'code-nl_BE-print' => 'value-print-nl',
            'code-fr_BE-print' => 'value-print-fr',
            'description-fr_BE' => 'descr-fr',
            'description-nl_BE' => 'descr-nl',

            'products-RELATED' => 'asso-1',
            'products-IN_STORE' => 'asso-2',
        ],
    ];

    public function test_convert_to_csv_struct_and_back(): void
    {
        $item = $this->items[0];

        $headerContext = new AkeneoCsvHeaderContext();
        $converter = new AkeneoCsvToStructuredDataConverter($headerContext);
        $converter->setOptions(
            ['list' => [
                'code' => 'text',
                'description' => 'text'
            ]
        ]);

        $convertedItem = $converter->convert($item);
        $revertedItem = $converter->revert($convertedItem);

        $this->assertTrue($item == $revertedItem);
    }
}