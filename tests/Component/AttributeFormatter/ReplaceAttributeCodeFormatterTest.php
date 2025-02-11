<?php

namespace Component\AttributeFormatter;

use Misery\Component\AttributeFormatter\AttributeValueFormatter;
use Misery\Component\AttributeFormatter\MultiValuePresenterFormatter;
use Misery\Component\AttributeFormatter\PropertyFormatterRegistry;
use Misery\Component\AttributeFormatter\ReplaceAttributeCodeFormatter;
use Misery\Component\Reader\ItemCollection;
use Misery\Component\Source\Source;
use PHPUnit\Framework\TestCase;

class ReplaceAttributeCodeFormatterTest extends TestCase
{
    public function dataCollection()
    {
        return new ItemCollection([
            [
                'code' => 'red',
                'attribute' => 'color',
                'labels-nl_BE' => 'Rood',
            ],
            [
                'code' => 'green',
                'attribute' => 'color',
                'labels-nl_BE' => 'Groen',
            ],
            [
                'code' => 'blue',
                'attribute' => 'color',
                'labels-nl_BE' => 'Blauw',
            ],
        ]);
    }

    public function testFormatWithStringValue()
    {
        $registry = new PropertyFormatterRegistry();
        $registry->addAll(
            new ReplaceAttributeCodeFormatter(
                Source::createSimple($this->dataCollection(), 'simple-source')
            ),
            new MultiValuePresenterFormatter(),
        );
        $formatter = new AttributeValueFormatter($registry);
        $formatter->setAttributeTypesAndCodes([
            'color' => 'pim_catalog_simpleselect',
        ]);

        $context = [
            'source' => 'simple-source',
            'filter' => [
                'attribute' => '{attribute-code}',
                'code' => '{value}',
            ],
            'return' => 'labels-nl_BE',
            'current-attribute-code' => 'color',
        ];

        $value = 'red';
        $expectedResult = 'Rood';

        $formattedValue = $formatter->format('color', $value, $context);

        $this->assertEquals($expectedResult, $formattedValue);
    }

    public function testFormatWithArrayValue()
    {
        $registry = new PropertyFormatterRegistry();
        $registry->addAll(
            new ReplaceAttributeCodeFormatter(
                Source::createSimple($this->dataCollection(), 'simple-source')
            ),
            new MultiValuePresenterFormatter(),
        );
        $formatter = new AttributeValueFormatter($registry);
        $formatter->setAttributeTypesAndCodes([
            'color' => 'pim_catalog_multiselect',
        ]);

        $context = [
            'source' => 'simple-source',
            'filter' => [
                'attribute' => '{attribute-code}',
                'code' => '{value}',
            ],
            'value-separator' => '-',
            'return' => 'labels-nl_BE',
            'current-attribute-code' => 'color',
        ];

        $value = ['red', 'green'];
        $expectedResult = 'Rood-Groen';

        $formattedValue = $formatter->format('color', $value, $context);

        $this->assertEquals($expectedResult, $formattedValue);
    }
}
