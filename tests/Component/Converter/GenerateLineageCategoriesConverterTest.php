<?php

namespace Tests\Misery\Component\Converter;

use PHPUnit\Framework\TestCase;
use Misery\Component\Converter\GenerateLineageCategoriesConverter;
use Misery\Component\Reader\ItemCollection;

class GenerateLineageCategoriesConverterTest extends TestCase
{
    private GenerateLineageCategoriesConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new GenerateLineageCategoriesConverter();
    }

    public function testGetName(): void
    {
        $this->assertEquals('lineage/categories/api', $this->converter->getName());
    }

    public function testConvertWithDefaultOptions(): void
    {
        $item = [
            'code' => 'purchase_women_clothing_bottoms_leggings_legging_long',
            'parent' => 'purchase_women_clothing_bottoms_leggings',
            'lineage' => 'women|clothing|bottoms|leggings'
        ];

        $this->converter->setOptions([
            'prefix' => 'purchase_',
            'lineage_separator' => '|'
        ]);

        $result = $this->converter->convert($item);

        $expected = [
            ['code' => 'purchase_women', 'parent' => null],
            ['code' => 'purchase_women_clothing', 'parent' => 'purchase_women'],
            ['code' => 'purchase_women_clothing_bottoms', 'parent' => 'purchase_women_clothing'],
            ['code' => 'purchase_women_clothing_bottoms_leggings', 'parent' => 'purchase_women_clothing_bottoms'],
            ['code' => 'purchase_women_clothing_bottoms_leggings_legging_long', 'parent' => 'purchase_women_clothing_bottoms_leggings'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testConvertWithMasterOption(): void
    {
        $item = [
            'code' => 'purchase_women_clothing_bottoms_leggings_legging_long',
            'parent' => 'purchase_women_clothing_bottoms_leggings',
            'lineage' => 'women|clothing|bottoms|leggings'
        ];

        $this->converter->setOptions([
            'prefix' => 'purchase_',
            'lineage_separator' => '|',
            'master' => 'purchase'
        ]);

        $result = $this->converter->convert($item);

        $expected = [
            ['code' => 'purchase_women', 'parent' => 'purchase'],
            ['code' => 'purchase_women_clothing', 'parent' => 'purchase_women'],
            ['code' => 'purchase_women_clothing_bottoms', 'parent' => 'purchase_women_clothing'],
            ['code' => 'purchase_women_clothing_bottoms_leggings', 'parent' => 'purchase_women_clothing_bottoms'],
            ['code' => 'purchase_women_clothing_bottoms_leggings_legging_long', 'parent' => 'purchase_women_clothing_bottoms_leggings'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testLoadReturnsItemCollection(): void
    {
        $item = [
            'code' => 'purchase_women',
            'parent' => null,
            'lineage' => 'women'
        ];

        $this->converter->setOptions([
            'prefix' => 'purchase_',
            'lineage_separator' => '|'
        ]);

        $result = $this->converter->load($item);

        $this->assertInstanceOf(ItemCollection::class, $result);
        $this->assertEquals($this->converter->convert($item), $result->getItems());
    }

    public function testRevertReturnsOriginalItem(): void
    {
        $item = ['key' => 'value'];
        $this->assertEquals($item, $this->converter->revert($item));
    }

    public function testConvertWithCustomLineageSeparator(): void
    {
        $item = [
            'code' => 'purchase_women_clothing_bottoms_leggings_legging_long',
            'parent' => 'purchase_women_clothing_bottoms_leggings',
            'lineage' => 'women>clothing>bottoms>leggings'
        ];

        $this->converter->setOptions([
            'prefix' => 'purchase_',
            'lineage_separator' => '>'
        ]);

        $result = $this->converter->convert($item);

        $expected = [
            ['code' => 'purchase_women', 'parent' => null],
            ['code' => 'purchase_women_clothing', 'parent' => 'purchase_women'],
            ['code' => 'purchase_women_clothing_bottoms', 'parent' => 'purchase_women_clothing'],
            ['code' => 'purchase_women_clothing_bottoms_leggings', 'parent' => 'purchase_women_clothing_bottoms'],
            ['code' => 'purchase_women_clothing_bottoms_leggings_legging_long', 'parent' => 'purchase_women_clothing_bottoms_leggings'],
        ];

        $this->assertEquals($expected, $result);
    }
}