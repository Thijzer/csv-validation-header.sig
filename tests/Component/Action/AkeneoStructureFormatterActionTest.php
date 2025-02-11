<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Converter\Matcher;
use Misery\Component\Source\Source;
use Misery\Component\Source\SourceCollection;
use PHPUnit\Framework\TestCase;
use Misery\Component\Action\AkeneoStructureFormatterAction;
use Misery\Component\Configurator\Configuration;
use Misery\Component\Reader\ItemReader;

class AkeneoStructureFormatterActionTest extends TestCase
{
    private AkeneoStructureFormatterAction $formatter;
    private Configuration $configuration;

    protected function setUp(): void
    {
        $this->formatter = new AkeneoStructureFormatterAction();
        $this->configuration = $this->createMock(Configuration::class);
        $this->formatter->setConfiguration($this->configuration);
        $attributesMock = $this->createMock(ItemReader::class);
        $attributesMock->method('find')->willReturn(new ItemReader(new \ArrayIterator([
            ['code' => 'color', 'localizable' => true, 'scopable' => true]
        ])));
        $sourceMock = $this->createMock(Source::class);
        $sourceMock->method('getCachedReader')->willReturn($attributesMock);

        $sourcesMock = $this->createMock(SourceCollection::class);
        $sourcesMock->method('get')->willReturn($sourceMock);

        $this->configuration->method('getSources')->willReturn($sourcesMock);
    }

    public function testApplyWithLocalizableAndScopableField(): void
    {
        $item = [
            'color' => [
                ['lang' => 'en_US', 'data' => 'Red'],
                ['lang' => 'fr_FR', 'data' => 'Rouge'],
            ]
        ];

        $options = [
            'context' => [
                'active_locales' => ['en_US', 'fr_FR'],
                'active_scopes' => ['ecommerce', 'print'],
                'active_locales_per_channel' => [
                    'ecommerce' => ['en_US', 'fr_FR'],
                    'print' => ['en_US'],
                ],
            ],
            'restructure' => [
                [
                    'field' => 'color',
                    'structure' => [
                        'locale' => '{{lang}}',
                        'scope' => null,
                        'data' => '{{data}}',
                    ],
                    'struct' => 'localized_items_array',
                ]
            ],
            'matcher_structure' => true,
            'attributes_source' => 'attributes',
        ];

        $this->formatter->setOptions($options);

        $result = $this->formatter->apply($item);

        $expected = [
            "color-en_US-ecommerce" => "Red",
            "color-en_US-print" => "Red",
            "color-fr_FR-ecommerce" => "Rouge",
            'color' => [
                'values|color|en_US|ecommerce' => [
                    'locale' => 'en_US',
                    'scope' => 'ecommerce',
                    'data' => 'Red',
                    'matcher' => Matcher::create('values|color', 'en_US', 'ecommerce'),
                ],
                'values|color|fr_FR|ecommerce' => [
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce',
                    'data' => 'Rouge',
                    'matcher' => Matcher::create('values|color', 'fr_FR', 'ecommerce'),
                ],
                'values|color|en_US|print' => [
                    'locale' => 'en_US',
                    'scope' => 'print',
                    'data' => 'Red',
                    'matcher' => Matcher::create('values|color', 'en_US', 'print'),
                ],
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testApplyWithLocalizableAndScopableFieldNoStruct(): void
    {
        $item = [
            'color' => [
                ['lang' => 'en_US', 'data' => 'Red'],
                ['lang' => 'fr_FR', 'data' => 'Rouge'],
            ]
        ];

        $options = [
            'context' => [
                'active_locales' => ['en_US', 'fr_FR'],
                'active_scopes' => ['ecommerce', 'print'],
                'active_locales_per_channel' => [
                    'ecommerce' => ['en_US', 'fr_FR'],
                    'print' => ['en_US'],
                ],
            ],
            'restructure' => [
                [
                    'field' => 'color',
                    'structure' => [
                        'locale' => '{{lang}}',
                        'scope' => null,
                        'data' => '{{data}}',
                    ],
                    'struct' => 'localized_items_array',
                ]
            ],
            'matcher_structure' => false,
            'attributes_source' => 'attributes',
        ];

        $this->formatter->setOptions($options);

        $result = $this->formatter->apply($item);

        $expected = [
            "color-en_US-ecommerce" => "Red",
            "color-en_US-print" => "Red",
            "color-fr_FR-ecommerce" => "Rouge",
        ];

        $this->assertEquals($expected, $result);
    }

    public function testApplyWithNonLocalizableField(): void
    {
        $item = [
            'sku' => 'SKU123'
        ];

        $options = [
            'context' => [
                'active_locales' => ['en_US', 'fr_FR'],
                'active_scopes' => ['ecommerce'],
            ],
            'restructure' => [
                [
                    'field' => 'sku',
                    'structure' => [
                        'locale' => null,
                        'scope' => null,
                        'data' => '{{sku}}',
                    ],
                    'struct' => 'localized_items_array',
                ]
            ],
            'matcher_structure' => true,
            'attributes_source' => 'attributes',
        ];

        $this->formatter->setOptions($options);

        $result = $this->formatter->apply($item);

        // The item should remain unchanged as the field is not localizable
        $this->assertEquals($item, $result);
    }
}