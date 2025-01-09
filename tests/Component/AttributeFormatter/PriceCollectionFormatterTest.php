<?php

namespace Tests\Misery\Component\AttributeFormatter;

use Misery\Component\AttributeFormatter\AttributeValueFormatter;
use Misery\Component\AttributeFormatter\MetricAttributeFormatter;
use Misery\Component\AttributeFormatter\MultiValuePresenterFormatter;
use Misery\Component\AttributeFormatter\PriceCollectionFormatter;
use Misery\Component\AttributeFormatter\PropertyFormatterRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class PriceCollectionFormatterTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_should_price_collection_type_a_value(): void
    {
        $registry = new PropertyFormatterRegistry();
        $registry->add(new PriceCollectionFormatter());
        $registry->add(new MultiValuePresenterFormatter());
        $attributeValueFormatter = new AttributeValueFormatter($registry);

        $attributeValueFormatter->setAttributeTypesAndCodes([
            'price' => 'pim_catalog_price_collection'
        ]);

        $value = [
            ['amount' => '1.0000', 'currency' => 'EUR'],
            ['amount' => '2.0000', 'currency' => 'US']
        ];

        $context = ['currency' => 'EUR'];

        $this->assertSame(
            $attributeValueFormatter->format('price', $value, $context),
            '1 EUR'
        );

        $this->assertSame(
            $attributeValueFormatter->format('price', $value, $context+['format' => '%currency% %amount%']),
            'EUR 1'
        );

        $this->assertSame(
            $attributeValueFormatter->format('price', $value, $context+['format' => '%currency% %amount%', 'dec' => 2, 'dec_point' => ',']),
            'EUR 1,00'
        );
    }
}