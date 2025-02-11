<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\AkeneoValueFormatterAction;
use Misery\Component\Configurator\Configuration;
use Misery\Component\Converter\Matcher;
use Misery\Component\Reader\ItemCollection;
use Misery\Component\Source\Source;
use Misery\Component\Source\SourceCollection;
use PHPUnit\Framework\TestCase;

class AkeneoValueFormatterActionTest extends TestCase
{
    public function test_it_should_value_format_a_boolean(): void
    {
        $format = new AkeneoValueFormatterAction();

        $item = [
            'identifier' => '1234',
            'enabled' => '1', # 1 equals true
        ];

        $format->setOptions([
            'fields' => ['enabled'],
            'context' => [
                'pim_catalog_boolean' => [
                    'label' => [
                        'Y' => 'TRUE',
                        'N' => 'FALSE',
                    ],
                ],
            ],
            'format_key' => null,
            'filter_list' => [
                'enabled' => 'pim_catalog_boolean',
            ],
        ]);

        $this->assertEquals([
            'identifier' => '1234',
            'enabled' => 'TRUE',
        ], $format->apply($item));
    }

    public function test_it_should_value_format_a_boolean_std_data(): void
    {
        $format = new AkeneoValueFormatterAction();

        $item = [
            'identifier' => '1234',
            'values|enabled' => [
                'matcher' => Matcher::create('enabled'),
                'locale' => null,
                'scope' => null,
                'data' => true,
            ],
        ];

        $format->setOptions([
            'fields' => ['enabled'],
            'context' => [
                'pim_catalog_boolean' => [
                    'label' => [
                        'Y' => 'TRUE',
                        'N' => 'FALSE',
                    ],
                ],
            ],
            'format_key' => null,
            'filter_list' => [
                'enabled' => 'pim_catalog_boolean',
            ],
        ]);

        $this->assertEquals([
            'identifier' => '1234',
            'values|enabled' => [
                'matcher' => Matcher::create('enabled'),
                'locale' => null,
                'scope' => null,
                'data' => 'TRUE',
            ],
        ], $format->apply($item));
    }

    public function test_it_should_value_format_a_metric(): void
    {
        $format = new AkeneoValueFormatterAction();

        $item = [
            'identifier' => '1234',
            'length_cable' => [
                'unit' => 'METER',
                'amount' => 1.0000,
            ],
        ];

        $format->setOptions([
            'fields' => ['length_cable'],
            'context' => [
                'pim_catalog_metric' => [
                    'format' => '%amount% %unit%',
                ],
            ],
            'filter_list' => [
                'length_cable' => 'pim_catalog_metric',
            ],
        ]);

        $this->assertEquals([
            'identifier' => '1234',
            'length_cable' => '1 METER',
        ], $format->apply($item));
    }

    public function test_it_should_value_format_a_metric_std_data(): void
    {
        $format = new AkeneoValueFormatterAction();

        $item = [
            'identifier' => '1234',
            'values|length_cable' => [
                'matcher' => Matcher::create('length_cable'),
                'scope' => null,
                'locale' => null,
                'data' => [
                    'unit' => 'METER',
                    'amount' => 1.0000,
                ],
            ],
        ];

        $format->setOptions([
            'fields' => ['length_cable'],
            'context' => [
                'pim_catalog_metric' => [
                    'format' => '%amount% %unit%',
                ],
            ],
            'filter_list' => [
                'length_cable' => 'pim_catalog_metric',
            ],
        ]);

        $this->assertEquals([
            'identifier' => '1234',
            'values|length_cable' => [
                'matcher' => Matcher::create('length_cable'),
                'scope' => null,
                'locale' => null,
                'data' => '1 METER',
            ],
        ], $format->apply($item));
    }

    public function test_it_should_value_format_a_simple_select(): void
    {
        $collection = new SourceCollection('internal');
        $source = Source::createSimple(
            new ItemCollection([
                [
                    'attribute' => 'brand',
                    'code' => 'nike',
                    'labels' => [
                        'nl_BE' => 'Nike nl_be',
                        'fr_BE' => 'Nike fr_be',
                        'de_DE' => 'Nike de_de',
                        'en_US' => 'Nike en_us',
                    ],
                ],
                [
                    'attribute' => 'brand',
                    'code' => 'louis',
                    'labels' => [
                        'nl_BE' => 'Louis Vuitton nl_be',
                        'fr_BE' => 'Louis Vuitton fr_be',
                        'de_DE' => 'Louis Vuitton de_de',
                        'en_US' => 'Louis Vuitton en_us',
                    ],
                ],
            ]),
            'attribute_options',
        );
        $collection->add($source);

        $format = new AkeneoValueFormatterAction();
        $format->setConfiguration($configuration = new Configuration());
        $configuration->addSources($collection);

        $item = [
            'identifier' => '1234',
            'description' => 'LV',
            'brand' => 'louis',
        ];

        $format->setOptions([
            'fields' => ['brand'],
            'context' => [
                'pim_catalog_simpleselect' => [
                    'source' => 'attribute_options',
                    'filter' => [
                        'attribute' => '{attribute-code}',
                        'code' => '{value}',
                    ],
                    'return' => 'labels-nl_BE',
                ],
            ],
            'format_key' => null,
            'filter_list' => [
                'brand' => 'pim_catalog_simpleselect',
            ],
        ]);

        $this->assertEquals([
            'identifier' => '1234',
            'description' => 'LV',
            'brand' => 'Louis Vuitton nl_be',
        ], $format->apply($item));
    }

    public function test_it_should_value_format_a_simple_select_std_data(): void
    {
        $collection = new SourceCollection('internal');
        $source = Source::createSimple(
            new ItemCollection([
                [
                    'attribute' => 'brand',
                    'code' => 'nike',
                    'labels' => [
                        'nl_BE' => 'Nike nl_be',
                        'fr_BE' => 'Nike fr_be',
                        'de_DE' => 'Nike de_de',
                        'en_US' => 'Nike en_us',
                    ],
                ],
                [
                    'attribute' => 'brand',
                    'code' => 'louis',
                    'labels' => [
                        'nl_BE' => 'Louis Vuitton nl_be',
                        'fr_BE' => 'Louis Vuitton fr_be',
                        'de_DE' => 'Louis Vuitton de_de',
                        'en_US' => 'Louis Vuitton en_us',
                    ],
                ],
            ]),
            'attribute_options',
        );
        $collection->add($source);

        $format = new AkeneoValueFormatterAction();
        $format->setConfiguration($configuration = new Configuration());
        $configuration->addSources($collection);

        $item = [
            'identifier' => '1234',
            'description' => 'LV',
            'values|brand' => [
                'matcher' => Matcher::create('brand'),
                'scope' => null,
                'locale' => null,
                'data' => 'louis',
            ],
        ];

        $format->setOptions([
            'fields' => ['brand'],
            'context' => [
                'pim_catalog_simpleselect' => [
                    'source' => 'attribute_options',
                    'filter' => [
                        'attribute' => '{attribute-code}',
                        'code' => '{value}',
                    ],
                    'return' => 'labels-nl_BE',
                ],
            ],
            'format_key' => null,
            'filter_list' => [
                'brand' => 'pim_catalog_simpleselect',
            ],
        ]);

        $this->assertEquals([
            'identifier' => '1234',
            'description' => 'LV',
            'values|brand' => [
                'matcher' => Matcher::create('brand'),
                'scope' => null,
                'locale' => null,
                'data' => 'Louis Vuitton nl_be',
            ],
        ], $format->apply($item));
    }
}