<?php

namespace Misery\Component\Converter\Akeneo\Csv;

use Misery\Component\Akeneo\AkeneoTypeBasedDataConverter;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Common\Registry\Registry;
use Misery\Component\Converter\AkeneoCsvHeaderContext;
use Misery\Component\Converter\ConverterInterface;
use Misery\Component\Converter\InitConverterInterface;
use Misery\Component\Converter\Matcher;
use Misery\Component\Decoder\ItemDecoder;
use Misery\Component\Decoder\ItemDecoderFactory;
use Misery\Component\Encoder\ItemEncoder;
use Misery\Component\Encoder\ItemEncoderFactory;
use Misery\Component\Format\ArrayFlattenFormat;
use Misery\Component\Format\ArrayGroupFormat;
use Misery\Component\Format\ArrayListFormat;
use Misery\Component\Format\StringToBooleanFormat;
use Misery\Component\Format\StringToIntFormat;
use Misery\Component\Format\StringToListFormat;
use Misery\Component\Mapping\ColumnMapper;
use Misery\Component\Modifier\ArrayFlattenModifier;
use Misery\Component\Modifier\ArrayUnflattenModifier;
use Misery\Component\Modifier\NullifyEmptyStringModifier;

class AkeneoProductCreator implements ConverterInterface, InitConverterInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private AkeneoTypeBasedDataConverter $akeneoDataStructure;

    private ItemEncoder $encoder;
    private ItemDecoder $decoder;

    private $options = [
        'container' => 'values', # this should always be values

        'attributes:list' => null,
        'attribute_types:list' => [], # this key value list is optional, improves type matching for options, metrics, prices
        'localizable_attribute_codes:list' => [],
        'scopable_attribute_codes:list' => [],
        'default_metrics:list' => [],
        'attribute_option_label_codes:list' => [],
        'set_default_metrics' => false,
        'default_locale' => null,
        'locales_mappings' => [],
        'active_locales' => [],
        'default_scope' => null,
        'default_currency' => null,
        'single_currency' => true,

        'identifier' => 'sku',
        'associations' => [], # RELATED
        'mapping' => [
            'product_value_keys' => [],
            'product_property_keys' => [
                'identifier' => 'id',
            ],
        ],
        'foreign_properties' => [],
        'foreign_parse' => [],
        'akeneo_properties' => [
            'sku' => [
                'text' => null,
            ],
            'enabled' => [
                'boolean' => null,
            ],
            'family' => [
                'text' => null,
            ],
            'categories'=> [
                'list' => null,
            ],
            'parent' => [
                'text' => null,
            ],
        ],
        'akeneo_parse' => [],
    ];

    public function init(): void
    {
        $this->akeneoDataStructure = new AkeneoTypeBasedDataConverter(
            $this->getOption('attribute_types:list'),
            $this->getOption('attributes:list'),
            $this->getOption('default_metrics:list'),
            $this->getOption('localizable_attribute_codes:list'),
            $this->getOption('scopable_attribute_codes:list'),
            null,
            $this->getOption('option_label'),
            $this->getOption('default_locale'),
            $this->getOption('default_scope'),
            $this->getOption('default_currency'),
        );

        list($encoder, $decoder) = $this->ItemEncoderDecoderFactory();
        $this->encoder = $encoder->createItemEncoder([
            'encode' => $this->getOption('foreign_properties'),
            'parse' => $this->getOption('foreign_parse'),
        ]);
        $this->decoder = $decoder->createItemDecoder([
            'decode' => $this->getOption('akeneo_properties'),
            'parse' => $this->getOption('akeneo_parse'),
        ]);
    }

    private function ItemEncoderDecoderFactory(): array
    {
        $encoderFactory = new ItemEncoderFactory();
        $decoderFactory = new ItemDecoderFactory();

        $formatRegistry = new Registry('format');
        $formatRegistry
            ->register(StringToListFormat::NAME, new StringToListFormat())
            ->register(StringToBooleanFormat::NAME, new StringToBooleanFormat())
            ->register(ArrayGroupFormat::NAME, new ArrayGroupFormat())
            ->register(StringToIntFormat::NAME, new StringToIntFormat())
            ->register(ArrayListFormat::NAME, new ArrayListFormat())
            ->register(ArrayFlattenFormat::NAME, new ArrayFlattenFormat())
        ;
        $modifierRegistry = new Registry('modifier');
        $modifierRegistry
            ->register(NullifyEmptyStringModifier::NAME, new NullifyEmptyStringModifier())
            ->register(ArrayUnflattenModifier::NAME, new ArrayUnflattenModifier())
            ->register(ArrayFlattenModifier::NAME, new ArrayFlattenModifier())
        ;

        $encoderFactory->addRegistry($formatRegistry);
        $encoderFactory->addRegistry($modifierRegistry);
        $decoderFactory->addRegistry($formatRegistry);
        $decoderFactory->addRegistry($modifierRegistry);

        return [$encoderFactory, $decoderFactory];
    }

    public function convert(array $item): array
    {
        $container = $this->getOption('container');
        $identifier = $this->getOption('identifier');
        $codes = $this->getOption('attribute_types:list');
        $keyCodes = is_array($codes) ? array_keys($codes): null;

        $item = $this->encoder->encode($item);
        $item = (new ColumnMapper())->map($item, array_flip($this->getOption('mapping')['product_value_keys']));

        $output = [];
        $output['identifier'] = $item[$identifier];

        if ($output['identifier'] === null) {
            return $output;
        }

        $separator = '-';
        foreach ($item as $key => $value) {

            $keys = explode($separator, $key);
            $masterKey = $keys[0];

            if (in_array($masterKey, $this->getOption('associations'))) {
                $output['associations'][$masterKey][$keys[1]] = array_filter(explode(',', $value));
                unset($item[$key]);
                continue;
            }

            if (in_array($masterKey, array_keys($this->getOption('mapping')['product_property_keys']))) {
                continue;
            }

            if (!in_array($masterKey, array_keys($this->getOption('mapping')['product_value_keys']))) {
                continue;
            }

            if ($keyCodes && false === in_array($masterKey, $keyCodes)) {
                continue;
            }

            if ($identifier === $key) {
                continue;
            }

            $prep = $this->akeneoDataStructure->getAkeneoDataStructure($key, $value);
            $prep['data'] = $value;

            $matcher = Matcher::create($container.'|'.$masterKey, $prep['locale'], $prep['scope']);
            unset($prep['key']); // old way of storing the original key
            $output[$k = $matcher->getMainKey()] = $prep;
            $output[$k]['matcher'] = $matcher;

            unset($item[$key]);
        }

        return $output;
    }

    public function revert(array $item): array
    {
        $container = $this->getOption('container');
        $identifier = $this->getOption('identifier');

        $output = [];
        $output[$identifier] = $item[$identifier] ?? $item['identifier'] ?? null;
        if (isset($item['enabled'])) {
            $output['enabled'] = $item['enabled'];
        }
        if (array_key_exists('family', $item)) {
            $output['family'] = $item['family'];
        }
        if (array_key_exists('categories', $item)) {
            $output['categories'] = $item['categories'];
        }
        if (array_key_exists('parent', $item)) {
            $output['parent'] = $item['parent'];
        }
        $output = $this->decoder->decode($output);

        foreach ($item as $key => $itemValue) {
            $matcher = $itemValue['matcher'] ?? null;
            /** @var $matcher Matcher */
            if ($matcher && $matcher->matches($container)) {
                unset($itemValue['matcher']);
                unset($item[$key]);
                if (is_array($itemValue['data']) && array_key_exists('unit', $itemValue['data'])) {
                    $output[$matcher->getRowKey()] = $itemValue['data']['amount'];
                    $output[$matcher->getRowKey().'-unit'] = $itemValue['data']['unit'];
                    continue;
                }
                if (is_array($itemValue['data']) && isset($itemValue['data'][0]['amount'])) {
                    // CSV can accept 2 column types, price and price-EUR
                    // for safety we should restore the old key here
                    $output[$matcher->getRowKey()] = $itemValue['data'][0]['amount'];
                    continue;
                }
                if (is_array($itemValue['data'])) {
                    $output[$matcher->getRowKey()] = implode(',', $itemValue['data']);
                    continue;
                }
                if (isset($itemValue['data'])) {
                    $output[$matcher->getRowKey()] = $itemValue['data'];
                }
            }
        }

        return $output;
    }

    /**
     * COPY/PASTA \Misery\Component\Akeneo\AkeneoTypeBasedDataConverter
     */
    private function numberize($value)
    {
        if (is_integer($value)) {
            return $value;
        }
        if (is_float($value)) {
            return $value;
        }
        if (is_string($value)) {
            $posNum = str_replace(',', '.', $value);
            return is_numeric($posNum) ? $posNum: $value;
        }
    }

    public function getName(): string
    {
        return 'akeneo/product/creator';
    }
}