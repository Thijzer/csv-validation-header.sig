<?php

namespace Tests\Misery\Component\Converter;

use PHPUnit\Framework\TestCase;
use Misery\Component\Converter\ReadLoopItemCollectionConverter;
use Misery\Component\Reader\ItemCollection;
use Misery\Component\Encoder\ItemEncoder;

class ReadLoopItemCollectionConverterTest extends TestCase
{
    private ReadLoopItemCollectionConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new ReadLoopItemCollectionConverter();
    }

    public function testGetName(): void
    {
        $this->assertEquals('read/loop/items', $this->converter->getName());
    }

    public function testConvertWithoutLoopItem(): void
    {
        $item = ['key' => 'value'];
        $this->converter->setOptions(['loop_item' => 'non_existent']);

        $result = $this->converter->convert($item);

        $this->assertEquals($item, $result);
    }

    public function testConvertWithLoopItem(): void
    {
        $item = [
            'root' => 'value',
            'loop_items' => [
                ['sub1' => 'value1'],
                ['sub2' => 'value2'],
            ]
        ];
        $this->converter->setOptions([
            'loop_item' => 'loop_items',
            'join_with' => ['root']
        ]);

        $result = $this->converter->convert($item);

        $expected = [
            [
                'root' => 'value',
                'sub1' => 'value1'
            ],
            [
                'root' => 'value',
                'sub2' => 'value2'
            ]
        ];
        $this->assertEquals($expected, $result);
    }

    public function testLoadReturnsItemCollection(): void
    {
        $item = ['key' => 'value'];
        $this->converter->setOptions(['loop_item' => 'non_existent']);

        $result = $this->converter->load($item);

        $this->assertInstanceOf(ItemCollection::class, $result);
        $this->assertEquals($item, $result->getItems());
    }

    public function testInitCreatesItemEncoder(): void
    {
        $this->converter->init();

        $reflectionClass = new \ReflectionClass(ReadLoopItemCollectionConverter::class);
        $encoderProperty = $reflectionClass->getProperty('encoder');
        $encoderProperty->setAccessible(true);

        $this->assertInstanceOf(ItemEncoder::class, $encoderProperty->getValue($this->converter));
    }

    public function testRevertThrowsException(): void
    {
        $this->expectException(\Exception::class);
        $this->converter->revert([]);
    }

    public function testConvertWithParseAndProperties(): void
    {
        $item = [
            'code' => 'TRACY',
            'size_chart' => 'B_D',
            'sizeVariants' => [
                [
                    'commercialConversion'=> [],
                    'skuCode'=> '3512814.010',
                    'alternativeEanCodes'=> [],
                    'sizeCode'=> '010',
                    'eanCode'=> '5404039007842',
                    'sizeComment'=> 'Casual single (dames)=> XS',
                    'gridColumnNumber'=> 1,
                ],
                [
                    'commercialConversion'=> [],
                    'skuCode'=> '3512814.020',
                    'alternativeEanCodes'=> [],
                    'sizeCode'=> '020',
                    'eanCode'=> '5404039007859',
                    'sizeComment'=> 'Casual single (dames)=> S',
                    'gridColumnNumber'=> 2,
                ],
            ],
        ];
        $this->converter->setOptions([
            'loop_item' => 'sizeVariants',
            'join_with' => ['code', 'size_chart']
        ]);
        $this->converter->init();

        $result = $this->converter->convert($item);

        $this->assertEquals([
            [
                'code' => 'TRACY',
                'size_chart' => 'B_D',
                'commercialConversion'=> [],
                'skuCode'=> '3512814.010',
                'alternativeEanCodes'=> [],
                'sizeCode'=> '010',
                'eanCode'=> '5404039007842',
                'sizeComment'=> 'Casual single (dames)=> XS',
                'gridColumnNumber'=> 1,
            ],
            [
                'code' => 'TRACY',
                'size_chart' => 'B_D',
                'commercialConversion'=> [],
                'skuCode'=> '3512814.020',
                'alternativeEanCodes'=> [],
                'sizeCode'=> '020',
                'eanCode'=> '5404039007859',
                'sizeComment'=> 'Casual single (dames)=> S',
                'gridColumnNumber'=> 2,
            ],
        ], $result);
    }
}