<?php

namespace Tests\Misery\Component\Converter;

use Misery\Component\Akeneo\Header\AkeneoHeaderTypes;
use Misery\Component\Converter\BCItemsApiConverter;
use Misery\Component\Converter\Matcher;
use PHPUnit\Framework\TestCase;

class BCItemsApiConverterTest extends TestCase
{
    public function testConvert()
    {
        // Define test data
        $converter = new BCItemsApiConverter();
        $converter->setOptions([
            'expand' => ['itemUnitOfMeasuresPieces', 'itemtranslations', 'itemreferences', 'itemCategories', 'itemContributions'],
            'mappings:list' => [
                'itemtranslations' => [
                    'typeName' => 'type_name',
                    'itemDescriptionERP' => 'article_description_erp',
                ],
                'itemContributions' => [
                    'typeCode' => 'RECUPEL',
                ],
                'itemUnitOfMeasuresPieces' => [
                    'length' => 'package_length',
                    'width' => 'package_width',
                    'height' => 'package_height',
                    'weight' => 'package_weight',
                ],
                'itemUnitOfMeasuresPallet' => [
                    'length' => 'package_length',
                    'width' => 'package_width',
                    'height' => 'package_height',
                    'weight' => 'package_weight',
                    'qtyPerUnitOfMeasure' => 'amount_per_pallet',
                ],
                'itemUnitOfMeasuresColli' => [
                    'length' => 'package_length',
                    'width' => 'package_width',
                    'height' => 'package_height',
                    'weight' => 'package_weight',
                    'qtyPerUnitOfMeasure' => 'amount_per_pallet',
                ],
            ],
            'attributes:list' => [
                'sku', 'article_description_erp', 'unitPrice', 'package_length', 'package_width', 'package_height', 'package_weight', 'RECUPEL',
            ],
            'attribute_types:list' => [
                'package_length' => AkeneoHeaderTypes::METRIC,
                'package_width' => AkeneoHeaderTypes::METRIC,
                'package_height' => AkeneoHeaderTypes::METRIC,
                'article_description_erp' => AkeneoHeaderTypes::TEXT,
                'unitPrice' => AkeneoHeaderTypes::TEXT,
                'RECUPEL' => AkeneoHeaderTypes::SELECT,
            ],
            'localizable_attribute_codes:list' => [
                'article_description_erp'
            ],
            'scopable_attribute_codes:list' => [],
            'default_metrics:list' => ['package_length' => 'METER', 'package_width' => 'METER', 'package_height' => 'METER'],
            'attribute_option_label_codes:list' => [],
            'set_default_metrics' => TRUE,
            'default_locale' => 'en_US',
            'active_locales' => ['en_US', 'nl_BE'],
            'default_scope' => 'ecommerce',
            'default_currency' => 'EUR',
            'option_label' => 'label-nl_BE',
        ]);

        $item = json_decode(file_get_contents(__DIR__ . '/data/bc_article.json'), true);

        // Perform the conversion
        $result = $converter->convert($item);

        // Define the expected result
        $expectedResult = [
            'sku' => '1001000023',
            'categories' => ['1001'],
            'values|unitPrice' => [
                'locale' => null,
                'scope' => null,
                'data' =>  73.6,
                'matcher' => Matcher::create('values|unitPrice'),
            ],
            'values|article_description_erp' => [
                'locale' => 'en_US',
                'scope' => null,
                'data' =>  'Badkamerventilator met timer',
                'matcher' => Matcher::create('values|article_description_erp'),
            ],
            'values|package_length' => [
                'locale' => null,
                'scope' => null,
                'data' =>  [
                    'amount' => 0,
                    'unit' => 'METER',
                ],
                'matcher' => Matcher::create('values|package_length'),
            ],
            'values|package_width' => [
                'locale' => null,
                'scope' => null,
                'data' =>  [
                    'amount' => 0,
                    'unit' => 'METER',
                ],
                'matcher' => Matcher::create('values|package_width'),
            ],
            'values|package_height' => [
                'locale' => null,
                'scope' => null,
                'data' =>  [
                    'amount' => 0,
                    'unit' => 'METER',
                ],
                'matcher' => Matcher::create('values|package_height'),
            ],
            'values|RECUPEL' => [
                'locale' => null,
                'scope' => null,
                'data' => 'RECUPEL',
                'matcher' => Matcher::create('values|RECUPEL'),
            ],
        ];

        // Perform the assertions
        $this->assertEquals($expectedResult, $result);
    }
}
