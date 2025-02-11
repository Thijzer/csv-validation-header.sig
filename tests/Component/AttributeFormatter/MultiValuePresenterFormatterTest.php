<?php

namespace Tests\Misery\Component\AttributeFormatter;

use Misery\Component\AttributeFormatter\AttributeValueFormatter;
use Misery\Component\AttributeFormatter\MetricAttributeFormatter;
use Misery\Component\AttributeFormatter\MultiValuePresenterFormatter;
use Misery\Component\AttributeFormatter\PriceCollectionFormatter;
use Misery\Component\AttributeFormatter\PropertyFormatterRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class MultiValuePresenterFormatterTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_should_price_collection_type_a_value(): void
    {
        $registry = new PropertyFormatterRegistry();
        $registry->add(new MultiValuePresenterFormatter());
        $attributeValueFormatter = new AttributeValueFormatter($registry);
        $attributeValueFormatter->setAttributeTypesAndCodes([
            'list' => 'pim_catalog_simpleselect'
        ]);

        $value = ['a', 'b', 'c'];

        $context = ['value-separator' => ','];

        $this->assertSame(
            $attributeValueFormatter->format('list', $value, $context),
            'a,b,c'
        );

        $context = []; # default seperator ', '

        $this->assertSame(
            $attributeValueFormatter->format('list', $value, $context),
            'a, b, c'
        );

        $value = ['unit' => 'GRAM', 'amount' => 100, 'dosis' => 'per day'];


        $context = [
            'format' => '%amount% %unit% %dosis%',
        ];

        $this->assertSame(
            $attributeValueFormatter->format('list', $value, $context),
            '100 GRAM per day'
        );
    }
}