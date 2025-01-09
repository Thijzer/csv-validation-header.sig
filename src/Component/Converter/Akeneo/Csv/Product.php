<?php

namespace Misery\Component\Converter\Akeneo\Csv;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Common\Registry\Registry;
use Misery\Component\Converter\AkeneoCsvHeaderContext;
use Misery\Component\Converter\ConverterInterface;
use Misery\Component\Converter\Matcher;
use Misery\Component\Decoder\ItemDecoder;
use Misery\Component\Decoder\ItemDecoderFactory;
use Misery\Component\Encoder\ItemEncoder;
use Misery\Component\Encoder\ItemEncoderFactory;
use Misery\Component\Format\StringToBooleanFormat;
use Misery\Component\Format\StringToListFormat;

class Product implements ConverterInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private AkeneoCsvHeaderContext $csvHeaderContext;
    private ItemEncoder $encoder;
    private ItemDecoder $decoder;

    private $options = [
        'container' => 'values',
        'default_currency' => 'EUR',
        'single_currency' => true,
        'attribute_types:list' => null, # this key value list is optional, improves type matching for options, metrics, prices
        'identifier' => 'sku',
        'associations' => ['RELATED'],
        'properties' => [
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
        'parse' => [],
    ];

    public function __construct(AkeneoCsvHeaderContext $csvHeaderContext)
    {
        $this->csvHeaderContext = $csvHeaderContext;
        list($encoder, $decoder) = $this->ItemEncoderDecoderFactory();
        $this->encoder = $encoder->createItemEncoder([
            'encode' => $this->getOption('properties'),
            'parse' => $this->getOption('parse'),
        ]);
        $this->decoder = $decoder->createItemDecoder([
            'decode' => $this->getOption('properties'),
            'parse' => $this->getOption('parse'),
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
        ;

        $encoderFactory->addRegistry($formatRegistry);
        $decoderFactory->addRegistry($formatRegistry);

        return [$encoderFactory, $decoderFactory];
    }

    public function convert(array $item): array
    {
        $this->csvHeaderContext->unsetHeader();
        $identifier = $this->getOption('identifier');
        $codes = $this->getOption('attribute_types:list');
        $keyCodes = is_array($codes) ? array_keys($codes): null;
        $separator = '-';
        $output = [];

        $item = $this->encoder->encode($item);

        foreach ($item as $key => $value) {
            $keys = explode($separator, $key);
            $masterKey = $keys[0];

            if (in_array($masterKey, $this->getOption('associations'))) {
                $output['associations'][$masterKey][$keys[1]] = array_filter(explode(',', $value));
                unset($item[$key]);
                continue;
            }

            if (in_array($masterKey, array_keys($this->getOption('properties')))) {
                continue;
            }

            if ($keyCodes && false === in_array($masterKey, $keyCodes)) {
                continue;
            }

            if ($identifier === $key) {
                continue;
            }

            if (str_ends_with($key, '-unit') !== false) {
                unset($item[$key]);
                continue;
            }

            # values
            $prep = $this->csvHeaderContext->create($item)[$key];
            $prep['data'] = $value;

            if (is_array($codes)) {
                # metrics
                if ($codes[$masterKey] === 'pim_catalog_metric') {
                    $prep['data'] = [
                        'amount' => $value,
                        'unit' => $item[str_replace($masterKey, $masterKey.'-unit', $key)] ?? null,
                    ];
                }
                # reference_data_multiselect
                if ($codes[$masterKey] === 'pim_reference_data_multiselect') {
                    $prep['data'] = str_replace('-', '_',  $prep['data']);
                    $prep['data'] = array_filter(explode(',', $prep['data']));
                }
                if ($codes[$masterKey] === 'pim_catalog_simpleselect') {
                    $prep['data'] = str_replace('-', '_',  $prep['data']);
                }
                if ($codes[$masterKey] === 'pim_reference_data_simpleselect') {
                    $prep['data'] = str_replace('-', '_',  $prep['data']);
                }
                # multiselect
                if ($codes[$masterKey] === 'pim_catalog_multiselect') {
                    $prep['data'] = str_replace('-', '_',  $prep['data']);
                    $prep['data'] = array_filter(explode(',', $prep['data']));
                }
                # pim_catalog_price_collection | single default currency EUR | will work in most cases
                if ($codes[$masterKey] === 'pim_catalog_price_collection' && true === $this->getOption('single_currency')) {
                    $prep['data'] = [['amount' => $prep['data'], 'currency' => $this->getOption('default_currency')]];
                }
                # boolean expecting CSV values
                if ($codes[$masterKey] === 'pim_catalog_boolean') {
                    if ($prep['data'] === '0') {
                        $prep['data'] = false;
                    }
                    if ($prep['data'] === '1') {
                        $prep['data'] = true;
                    }
                }
                # number
                if ($codes[$masterKey] === 'pim_catalog_number') {
                    $prep['data'] = (is_string($prep['data'])) ? $this->numberize($prep['data']): $prep['data'];
                }
            }

            $matcher = Matcher::create('values|'.$masterKey, $prep['locale'], $prep['scope']);
            unset($prep['key']); // old way of storing the original key
            $output[$k = $matcher->getMainKey()] = $prep;
            $output[$k]['matcher'] = $matcher;

            unset($item[$key]);
        }

        return $item+$output;
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
        return 'akeneo/product/csv';
    }
}