<?php

namespace Tests\Misery\Component\AttributeFormatter;

use Misery\Component\AttributeFormatter\AttributeValueFormatter;
use Misery\Component\AttributeFormatter\MetricAttributeFormatter;
use Misery\Component\AttributeFormatter\MultiValuePresenterFormatter;
use Misery\Component\AttributeFormatter\PropertyFormatterRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class MetricAttributeFormatterTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_should_metric_type_a_value(): void
    {
        $registry = new PropertyFormatterRegistry();
        $registry->add(new MetricAttributeFormatter());
        $registry->add(new MultiValuePresenterFormatter());
        $attributeValueFormatter = new AttributeValueFormatter($registry);
        $attributeValueFormatter->setAttributeTypesAndCodes([
            'weight' => 'pim_catalog_metric',
        ]);

        $value = ['amount' => '1', 'unit' => 'GRAM'];

        $context = [
            'pim_catalog_metric' => [
                    'format' => '%amount% %unit%',
            ]
        ];

        $this->assertSame(
            $attributeValueFormatter->format('weight', $value, $context),
            '1 GRAM'
        );

        $context = ['map' => ['GRAM' => 'gr']];

        $this->assertSame(
            $attributeValueFormatter->format('weight', $value, $context),
            '1 gr'
        );

        $this->assertSame(
            $attributeValueFormatter->format('weight', $value, $context+['metric-display-unit' => false]),
            '1'
        );

        $this->assertSame(
            $attributeValueFormatter->format('weight', $value, $context+['metric-separator' => '']),
            '1gr'
        );
    }
}