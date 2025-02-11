<?php

namespace Misery\Component\Converter;

use Misery\Component\Akeneo\AkeneoTypeBasedDataConverter;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Reader\ReaderAwareInterface;
use Misery\Component\Reader\ReaderAwareTrait;

/**
 * This Converter converts BC product Data to std data with Matcher
 * We focus on correcting without manipulation the data
 * The better the input you give the better the output
 * Besides data values, we also map locales and channels
 */
class BCItemsApiConverter implements ConverterInterface, ReaderAwareInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;
    use ReaderAwareTrait;

    private $options = [
        'expand' => [],
        'mappings:list' => [],
        'attributes:list' => [],
        'attribute_types:list' => [],
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
        'container' => 'values',
        'option_label' => 'label-nl_BE',
    ];

    private $decoder;
    private ?AkeneoTypeBasedDataConverter $akeneoDataStructure = null;

    public function getAkeneoDataStructure(string $attributeCode, $value): array
    {
        if (null === $this->akeneoDataStructure) {
            $this->akeneoDataStructure = new AkeneoTypeBasedDataConverter(
                $this->getOption('attribute_types:list'),
                $this->getOption('attributes:list'),
                $this->getOption('default_metrics:list'),
                $this->getOption('localizable_attribute_codes:list'),
                $this->getOption('scopable_attribute_codes:list'),
                $this->reader,
                $this->getOption('option_label'),
                $this->getOption('default_locale'),
                $this->getOption('default_scope'),
                $this->getOption('default_currency'),
            );
        }

        return $this->akeneoDataStructure->getAkeneoDataStructure($attributeCode, $value);
    }

    public function convert(array $item): array
    {
        $expand = $this->getOption('expand');

        unset($item['@odata.etag']);
        $tmp['sku'] = $item['no'] ?? $item['number'];

        $this->processCharacteristics(
            $item,
            ['unitPrice', 'tariffNo', 'standard_delivery_time', 'gtin'],
            '',
            $tmp
        );

        // these mappings, link and match the BC data and convert it to std Data Matcher
        $mappings = $this->getOption('mappings:list');

        foreach ($expand as $expandOption) {
            foreach ($item[$expandOption] ?? [] as $itemProp) {

                if ($expandOption === 'itemCategories') {
                    $tmp['categories'] = array_map(function ($cat) {
                        return $cat['code'] ?? null;
                    }, $item[$expandOption]);
                    continue;
                }

                $bcMappings = $mappings[$expandOption] ?? null;
                if (!$bcMappings) {
                    continue;
                }

                $bcMappingKeys = array_keys($bcMappings);

                $this->processCharacteristics(
                    $itemProp,
                    $bcMappingKeys,
                    $expandOption,
                    $tmp
                );
            }
        }

        return $tmp;
    }

    private function processCharacteristics(array $itemProperty, array $characteristics, string $extendedOption, &$tmp): void
    {
        $container = $this->getOption('container');
        $mappings = $this->getOption('mappings:list');
        $localeMappings = $this->getOption('locales_mappings');
        $activeLocales = $this->getOption('active_locales');

        foreach ($characteristics as $characteristic) {
            $key = $mappings[$extendedOption][$characteristic] ?? $mappings[$characteristic] ?? $characteristic;
            $value = $itemProperty[$characteristic] ?? null;
            if ($value === null) {
                continue;
            }

            try {
                $value = $this->getAkeneoDataStructure($key, $value);
            } catch (\Exception) {
                continue;
            }

            if (empty($value['data'])) {
                continue;
            }

            $matcher = Matcher::create(
                $container.'|'.$key,
                $this->getValue('languageCode', $itemProperty, $localeMappings),
                $this->getValue('variantCode', $itemProperty),
            );

            if ($matcher->isLocalizable() && !in_array($matcher->getLocale(), $activeLocales)) {
                continue;
            }
            $tmp[$key = $matcher->getMainKey()] = $value;
            $tmp[$key]['matcher'] = $matcher;
        }
    }

    public function getValue(string $property, array $item, array $mappings = [], $default = null)
    {
        if (!empty($item[$property])) {
            return $mappings[$item[$property]] ?? $default;
        }

        return $default;
    }

    public function revert(array $item): array
    {
        $container = $this->getOption('container');

        $output = [];
        foreach ($item as $key => $itemValue) {
            $matcher = $itemValue['matcher'] ?? null;
            /** @var $matcher Matcher */
            if ($matcher && $matcher->matches($container)) {
                unset($itemValue['matcher']);
                unset($item[$key]);
                if (is_array($itemValue['data']) && array_key_exists('unit', $itemValue['data'])) {
                    $output[$matcher->getRowKey()] = $itemValue['data']['amount'];
                    $output[$matcher->getRowKey().'-unit'] = $itemValue['data']['unit'];
                }
                if (is_array($itemValue['data']) && array_key_exists('currency', $itemValue['data'])) {
                    $output[$matcher->getRowKey().'-'.$itemValue['data']['currency']] = $itemValue['data']['amount'];
                }
                if (is_string($itemValue['data']) || is_int($itemValue['data']) || is_float($itemValue['data'])) {
                    $output[$matcher->getRowKey()] = $itemValue['data'];
                }
            }
        }
        $item['categories'] = implode(',', $item['categories'] ?? []);

        return $item+$output;
    }

    public function getName(): string
    {
        return 'bc/items/api';
    }
}