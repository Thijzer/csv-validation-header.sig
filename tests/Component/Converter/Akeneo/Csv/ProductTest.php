<?php
declare(strict_types=1);

namespace Tests\Misery\Component\Converter\Akeneo\Csv;

use Misery\Component\Converter\Akeneo\Csv\Product;
use Misery\Component\Converter\AkeneoCsvHeaderContext;
use Misery\Component\Converter\Matcher;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    private array $inputData = [
        'sku' => 'SKU123',
        'enabled' => '1',
        'family' => 'Family1',
        'categories' => 'Category1,Category2',
        'parent' => 'ParentProduct',
        'attribute1' => 'Value1',
        'attribute2' => 'Value2',
        'attribute3' => '10',
        'attribute3-unit' => 'KILOGRAM',
        'attribute4' => 'option_a,option_b',
        'attribute5' => 'option_c',
        'attribute6' => 'option_a,option_b',
        'attribute7' => 'option_c',
        'attribute8' => '135',
        # 'attribute9' => 'unknown-value-to-akeneo',
        'attribute10-nl_BE' => 'Value1',
        'attribute11-nl_BE-ecommerce' => 'Value2',
    ];

    private function getNormalizedData(): array
    {
        return [
            'sku' => 'SKU123',
            'enabled' => true,
            'family' => 'Family1',
            'categories' => ['Category1','Category2'],
            'parent' => 'ParentProduct',
            'values|attribute1' => [
                'matcher' => Matcher::create('values|attribute1'),
                'locale' => null,
                'scope' => null,
                'data' => 'Value1',
            ],
            'values|attribute2' => [
                'matcher' => Matcher::create('values|attribute2'),
                'locale' => null,
                'scope' => null,
                'data' => 'Value2',
            ],
            'values|attribute3' => [
                'matcher' => Matcher::create('values|attribute3'),
                'locale' => null,
                'scope' => null,
                'data' => [
                    'amount' => '10',
                    'unit' => 'KILOGRAM',
                ],
            ],
            'values|attribute4' => [
                'matcher' => Matcher::create('values|attribute4'),
                'locale' => null,
                'scope' => null,
                'data' => ['option_a','option_b'],
            ],
            'values|attribute5' => [
                'matcher' => Matcher::create('values|attribute5'),
                'locale' => null,
                'scope' => null,
                'data' => 'option_c',
            ],
            'values|attribute6' => [
                'matcher' => Matcher::create('values|attribute6'),
                'locale' => null,
                'scope' => null,
                'data' => ['option_a','option_b'],
            ],
            'values|attribute7' => [
                'matcher' => Matcher::create('values|attribute7'),
                'locale' => null,
                'scope' => null,
                'data' => 'option_c',
            ],
            'values|attribute8' => [
                'matcher' => Matcher::create('values|attribute8'),
                'locale' => null,
                'scope' => null,
                'data' => [['amount' => 135, 'currency' => 'EUR']],
            ],
            # 'attribute9' => 'unknown-value-to-akeneo', # not linked values are never handled correctly, we should fail
            'values|attribute10|nl_BE' => [
                'matcher' => Matcher::create('values|attribute10', 'nl_BE'),
                'locale' => 'nl_BE',
                'scope' => null,
                'data' => 'Value1',
            ],
            'values|attribute11|nl_BE|ecommerce' => [
                'matcher' => Matcher::create('values|attribute11', 'nl_BE', 'ecommerce'),
                'locale' => 'nl_BE',
                'scope' => 'ecommerce',
                'data' => 'Value2',
            ],
        ];
    }

    public function testConvert()
    {
        $csvHeaderContext = new AkeneoCsvHeaderContext();
        $converter = new Product($csvHeaderContext);
        $converter->setOptions([
            'attribute_types:list' => [
                'attribute1' => 'pim_catalog_text',
                'attribute2' => 'pim_catalog_text',
                'attribute3' => 'pim_catalog_metric',
                'attribute4' => 'pim_catalog_multiselect',
                'attribute5' => 'pim_catalog_simpleselect',
                'attribute6' => 'pim_reference_data_multiselect',
                'attribute7' => 'pim_reference_data_simpleselect',
                'attribute8' => 'pim_catalog_price_collection',
                'attribute10' => 'pim_catalog_text',
                'attribute11' => 'pim_catalog_text',
            ],
        ]);

        $this->assertEquals($this->getNormalizedData(), $converter->convert($this->inputData));
    }

    public function testRevert()
    {
        $csvHeaderContext = new AkeneoCsvHeaderContext();
        $converter = new Product($csvHeaderContext);

        $this->assertEquals($this->inputData, $converter->revert($this->getNormalizedData()));
    }

    public function testName()
    {
        $csvHeaderContext = new AkeneoCsvHeaderContext();
        $converter = new Product($csvHeaderContext);

        $this->assertEquals('akeneo/product/csv', $converter->getName());
    }
}
