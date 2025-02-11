<?php

namespace Tests\Misery\Component\Converter\Akeneo;

use Misery\Component\Converter\Akeneo\AkeneoOptionExtractor;
use Misery\Component\Reader\ItemCollection;
use PHPUnit\Framework\TestCase;

class AkeneoOptionExtractorTest extends TestCase
{
    public function testConvert()
    {
        $extractor = new AkeneoOptionExtractor();
        $extractor->setOptions([
            'attribute_option_codes:list' => ['color', 'size', 'brand'],
        ]);

        $item = [
            'color' => ['Red', 'Blue'],
            'size' => ['L', 'M'],
            'brand' => 'nike',
        ];

        $expected = [
            'red-color' => [
                'attribute' => 'color',
                'code' => 'red',
                'reference' => 'red-color',
            ],
            'blue-color' => [
                'attribute' => 'color',
                'code' => 'blue',
                'reference' => 'blue-color',
            ],
            'l-size' => [
                'attribute' => 'size',
                'code' => 'l',
                'reference' => 'l-size',
            ],
            'm-size' => [
                'attribute' => 'size',
                'code' => 'm',
                'reference' => 'm-size',
            ],
            'nike-brand' => [
                'attribute' => 'brand',
                'code' => 'nike',
                'reference' => 'nike-brand',
            ]
        ];


        $this->assertEquals($expected, $extractor->convert($item));
    }

    public function testLoad()
    {
        $extractor = new AkeneoOptionExtractor();
        $extractor->setOptions([
            'attribute_option_codes:list' => ['color', 'size'],
            'parent_identifier' => 'attribute',
            'identifier' => 'code',
            'reference' => 'reference',
            'reference_code' => true,
            'lower_cased' => true,
        ]);

        $item = [
            'color' => ['Red', 'Blue'],
            'size' => ['L', 'M'],
        ];

        $expected = new ItemCollection([
            'red-color' => [
                'attribute' => 'color',
                'code' => 'red',
                'reference' => 'red-color',
            ],
            'blue-color' => [
                'attribute' => 'color',
                'code' => 'blue',
                'reference' => 'blue-color',
            ],
            'l-size' => [
                'attribute' => 'size',
                'code' => 'l',
                'reference' => 'l-size',
            ],
            'm-size' => [
                'attribute' => 'size',
                'code' => 'm',
                'reference' => 'm-size',
            ],
        ]);

        $this->assertEquals($expected, $extractor->load($item));
    }
}