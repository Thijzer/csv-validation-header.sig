<?php

namespace Tests\Misery\Component\Encoding;

use Misery\Component\Action\ReplaceAction;
use Misery\Component\Common\Registry\Registry;
use Misery\Component\Decoder\ItemDecoderFactory;
use Misery\Component\Encoder\ItemEncoderFactory;
use Misery\Component\Format\ArrayGroupFormat;
use Misery\Component\Format\ArrayListFormat;
use Misery\Component\Format\StringToBooleanFormat;
use Misery\Component\Format\StringToIntFormat;
use Misery\Component\Format\StringToListFormat;
use Misery\Component\Modifier\ArrayUnflattenModifier;
use Misery\Component\Modifier\NullifyEmptyStringModifier;
use Misery\Component\Reader\ItemCollection;
use Misery\Component\Reader\ItemReader;
use PHPUnit\Framework\TestCase;

class EncodingTest extends TestCase
{
    private array $items = [
        [
            'id' => '1',
            'first_name' => 'Gordie',
            'codes' => 'A,B,C,D',
        ],
        [
            'id' => '2',
            'first_name' => 'Frans',
            'codes' => 'E,F,G',
        ],
        [
            'id' => '3',
            'first_name' => 'Dolly',
            'codes' => '',
        ],
    ];

    private function itemEncoderFactory(): ItemEncoderFactory
    {
        $encoderFactory = new ItemEncoderFactory();

        $formatRegistry = new Registry('format');
        $formatRegistry
            ->register(ArrayGroupFormat::NAME, new ArrayGroupFormat())
            ->register(StringToListFormat::NAME, new StringToListFormat())
            ->register(StringToIntFormat::NAME, new StringToIntFormat())
            ->register(StringToBooleanFormat::NAME, new StringToBooleanFormat())
            ->register(ArrayListFormat::NAME, new ArrayListFormat())
        ;
        $modifierRegistry = new Registry('modifier');
        $modifierRegistry
            ->register(NullifyEmptyStringModifier::NAME, new NullifyEmptyStringModifier())
        ;

        $encoderFactory->addRegistry($modifierRegistry);
        $encoderFactory->addRegistry($formatRegistry);

        return $encoderFactory;
    }

    private function itemDecoderFactory(): ItemDecoderFactory
    {
        $encoderFactory = new ItemDecoderFactory();

        $formatRegistry = new Registry('format');
        $formatRegistry
            ->register(ArrayGroupFormat::NAME, new ArrayGroupFormat())
            ->register(StringToListFormat::NAME, new StringToListFormat())
            ->register(StringToIntFormat::NAME, new StringToIntFormat())
            ->register(StringToBooleanFormat::NAME, new StringToBooleanFormat())
            ->register(ArrayListFormat::NAME, new ArrayListFormat())
        ;
        $modifierRegistry = new Registry('modifier');
        $modifierRegistry
            ->register(NullifyEmptyStringModifier::NAME, new NullifyEmptyStringModifier())
        ;

        $encoderFactory->addRegistry($modifierRegistry);
        $encoderFactory->addRegistry($formatRegistry);

        return $encoderFactory;
    }

    public function test_encode_item_attribute_csv_to_std(): void
    {
        $ruleSet = [
            'encode' => [
                'unique' => [
                    'boolean' => null,
                ],
                'useable_as_grid_filter' => [
                    'boolean' => null,
                ],
                'localizable' => [
                    'boolean' => null,
                ],
                'scopable' => [
                    'boolean' => null,
                ],
                'is_read_only' => [
                    'boolean' => null,
                ],
                'decimals_allowed' => [
                    'boolean' => null,
                ],
                'allowed_extensions' => [
                    'list' => null,
                ],
                'sort_order' => [
                    'integer' => null,
                ],
                'available_locales' => [
                    'list' => null,
                ],
                'guidelines' => [
                    'list' => null,
                ],
                'labels' => [
                    'group' => null,
                ],
                'group_labels' => [
                    'group' => null,
                ],
            ],
            'parse' => [
                'nullify' => null,
            ]
        ];
        $ruleSet['decode'] = $ruleSet['encode'];

        $attributeEncoder = $this->itemEncoderFactory()->createItemEncoder($ruleSet);

        $item = [
            'code' => 'sku',
            'label-de_DE' => 'SKU',
            'label-en_US' => 'SKU',
            'label-fr_FR' => 'SKU',
            'allowed_extensions' => '',
            'auto_option_sorting' => '',
            'available_locales' => '',
            'date_max' => '',
            'date_min' => '',
            'decimals_allowed' => '0',
            'default_metric_unit' => '',
            'group' => '',
            'localizable' => '0',
            'max_characters' => '',
            'max_file_size' => '1',
            'metric_family' => 'pim_catalog_identifier',
            'minimum_input_length' => '1',
            'negative_allowed' => '',
            'number_max' => '',
            'number_min' => '',
            'reference_data_name' => '',
            'scopable' => '0',
            'sort_order' => '0',
            'type' => '',
            'unique' => '',
            'useable_as_grid_filter' => '',
            'validation_regexp' => '',
            'validation_rule' => '',
            'wysiwyg_enabled' => '',
        ];

        $encodedItem = $attributeEncoder->encode($item);

        $this->assertSame([
            'code' => 'sku',
            'label-de_DE' => 'SKU',
            'label-en_US' => 'SKU',
            'label-fr_FR' => 'SKU',
            'allowed_extensions' => [],
            'auto_option_sorting' => null,
            'available_locales' => [],
            'date_max' => null,
            'date_min' => null,
            'decimals_allowed' => false,
            'default_metric_unit' => null,
            'group' => null,
            'localizable' => false,
            'max_characters' => null,
            'max_file_size' => '1',
            'metric_family' => 'pim_catalog_identifier',
            'minimum_input_length' => '1',
            'negative_allowed' => null,
            'number_max' => null,
            'number_min' => null,
            'reference_data_name' => null,
            'scopable' => false,
            'sort_order' => 0,
            'type' => null,
            'unique' => null,
            'useable_as_grid_filter' => null,
            'validation_regexp' => null,
            'validation_rule' => null,
            'wysiwyg_enabled' => null,
        ],
            $encodedItem
        );

        $attributeDecoder = $this->itemDecoderFactory()->createItemDecoder($ruleSet);

        $this->assertSame($item, $attributeDecoder->decode($encodedItem));
    }

    public function test_encode_item_string_to_list_mod(): void
    {
        $setA = new ItemCollection($this->items);
        $setB = clone $setA;
        $setB->set(1, [
            'id' => '2',
            'codes' => 'E,F,G,H',
        ]);

        $encoderFactory = new ItemEncoderFactory();

        $formatRegistry = new Registry('format');
        $formatRegistry
            ->register(StringToListFormat::NAME, new StringToListFormat())
            ->register(StringToIntFormat::NAME, new StringToIntFormat())
        ;

        $encoderFactory->addRegistry($formatRegistry);

        $encoder = $encoderFactory->createItemEncoder([
            'encode' => [
                'id' => [
                    'integer' => []
                ],
                'codes' => [
                    'list' => []
                ],
            ]
        ]);

        $encodedItem = $encoder->encode($this->items[0]);

        $this->assertSame($encodedItem, [
            'id' => 1,
            'first_name' => 'Gordie',
            'codes' => ['A','B','C','D'],
        ]);
    }

    public function test_encode_item_str_to_null_mod(): void
    {
        $setA = new ItemCollection($this->items);
        $setB = clone $setA;
        $setB->set(1, [
            'id' => '2',
            'codes' => 'E,F,G,H',
        ]);

        $encoderFactory = new ItemEncoderFactory();

        $modifierRegistry = new Registry('modifier');
        $modifierRegistry
            ->register(NullifyEmptyStringModifier::NAME, new NullifyEmptyStringModifier())
        ;

        $encoderFactory->addRegistry($modifierRegistry);

        $encoder = $encoderFactory->createItemEncoder([
            'parse' => [
                'nullify' => [],
            ]
        ]);

        $encodedItem = $encoder->encode($this->items[2]);

        $this->assertSame($encodedItem, [
            'id' => '3',
            'first_name' => 'Dolly',
            'codes' => null,
        ]);
    }

    public function test_hard_akeneo_examples_test()
    {
        $category = [
            [
                'code' => 'AB',
                'label' => [
                    'nl_BE' => 'AB-nlBE',
                    'fr_BE' => 'AB-frBE',
                ],
            ],
            [
                'code' => 'CD',
                'label' => [
                    'nl_BE' => 'CD-nlBE',
                    'fr_BE' => 'CD-frBE',
                ],
            ],
            [
                'code' => 'EF',
                'label' => [
                    'nl_BE' => 'EF-nlBE',
                    'fr_BE' => 'EF-frBE',
                ],
            ],
            [
                'code' => 'GH',
                'label' => [
                    'nl_BE' => 'GH-nlBE',
                    'fr_BE' => 'GH-frBE',
                ],
            ],
            [
                'code' => 'IJ',
                'label' => [
                    'nl_BE' => 'IJ-nlBE',
                    'fr_BE' => 'IJ-frBE',
                ],
            ],
        ];

        $item = ['sku' => '8604597', 'categories' => 'AB,CD,EF,GH,IJ', 'enabled' => '1', 'family' => 'para'];

        $modifierRegistry = new Registry('modifier');
        $modifierRegistry
            ->register(ArrayUnflattenModifier::NAME, new ArrayUnflattenModifier())
        ;

        $formatRegistry = new Registry('format');
        $formatRegistry
            ->register(StringToListFormat::NAME, new StringToListFormat())
        ;

        $encoderFactory = new ItemEncoderFactory();
        $encoderFactory->addRegistry($modifierRegistry);
        $encoderFactory->addRegistry($formatRegistry);

        $encoder = $encoderFactory->createItemEncoder([
            'encode' => [
                'categories' => [
                    'list' => [],
                ],
            ],
        ]);

        $encodedItem = $encoder->encode($item);

        $reader = new ItemReader(new ItemCollection($category));
        $format = new ReplaceAction();
        $format->setReader($reader);

        $format->setOptions([
            'method' => 'getLabelsFromList',
            'locales' => ['nl_BE', 'fr_BE'],
            'key' => 'categories',
        ]);

        $convertedItem = $format->apply($encodedItem);

        $decoderFactory = new ItemDecoderFactory();
        $decoderFactory->addRegistry($formatRegistry);
        $decoderFactory->addRegistry($modifierRegistry);

        $decoder = $decoderFactory->createItemDecoder([
            'encode' => [
                'categories' => [
                    'list' => [],
                ],
            ],
            'parse' => [
                'unflatten' => [
                    'separator' => '-',
                ],
            ]
        ]);

        $this->assertSame(
            [
                "sku" => "8604597",
                "categories-nl_BE" => "AB-nlBE,CD-nlBE,EF-nlBE,GH-nlBE,IJ-nlBE",
                "categories-fr_BE" => "AB-frBE,CD-frBE,EF-frBE,GH-frBE,IJ-frBE",
                "enabled" => "1",
                "family" => "para",
            ],
            $decoder->decode($convertedItem)
        );
    }
}