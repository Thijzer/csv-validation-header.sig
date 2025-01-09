<?php

namespace Misery\Component\Converter;

use Misery\Component\Akeneo\Header\AkeneoHeaderTypes;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Reader\ReaderAwareInterface;
use Misery\Component\Reader\ReaderAwareTrait;

/**
 * This Converter converts flat product to std data
 * We focus on correcting with minimal issues
 * The better the input you give the better the output
 */
class AkeneoFlatProductToCsvConverter implements ConverterInterface, ReaderAwareInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;
    use ReaderAwareTrait;

    private $options = [
        'attributes:list' => [],
        'attribute_types:list' => [],
        'localizable_attribute_codes:list' => [],
        'scopable_attribute_codes:list' => [],
        'default_metrics:list' => [],
        'attribute_option_label_codes:list' => [],
        'set_default_metrics' => false,
        'filter_option_codes' => true,
        'default_locale' => null,
        'default_scope' => null,
        'default_currency' => null,
        'container' => 'values',
        'option_label' => 'label-nl_BE',
        'properties' => ['sku', 'family', 'parent'],
    ];

    private $decoder;

    public function convert(array $item): array
    {
        $tmp = [];
        $container = $this->getOption('container');
        // first we need to convert the values
        foreach ($item[$container] ?? $item as $key => $value) {

            list($masterKey, $context) = $this->createScopes($key);
            if (in_array($masterKey, ['sku', 'family', 'parent'])) {
                continue;
            }

            $value = $this->getAkeneoDataStructure($masterKey, $value, $context);
            if (is_array($value) && array_key_exists('data', $value)) {
                $matcher = Matcher::create($container.'|'.$masterKey, $value['locale'], $value['scope']);
                $tmp[$k = $matcher->getMainKey()] = $value;
                $tmp[$k]['matcher'] = $matcher;
                unset($item[$key]);
            }
        }
        unset($item[$container]);

        return $item+$tmp;
    }

    /**
     * short_description-en_US-print
     *
     * becomes
     * masterKey short_description
     * context [
     *   locale => en_US,
     *   scope => print,
     */
    private function createScopes(string $key, $separator = '-'): array
    {
        $explodedKeys = explode($separator, $key);

        return [$explodedKeys[0], [
            'locale' => $explodedKeys[1] ?? null,
            'scope' => $explodedKeys[2] ?? null,
        ]];
    }

    public function getAkeneoDataStructure(string $attributeCode, $value, array $context)
    {
        $type = $this->getOption('attribute_types:list')[$attributeCode] ?? null;
        if (null === $type) {
            return $value;
        }

        if (is_array($value)) {
            if (
                array_key_exists('locale', $value) &&
                array_key_exists('data', $value) &&
                array_key_exists('scope', $value)
            ) {
                return $value;
            }
        }

        $localizable = in_array(
            $attributeCode,
            $this->getOption('localizable_attribute_codes:list')
        );
        $scopable = in_array(
            $attributeCode,
            $this->getOption('scopable_attribute_codes:list')
        );

        switch ($type) {
            case AkeneoHeaderTypes::TEXT:
                // no changes
                break;
            case AkeneoHeaderTypes::NUMBER:
                $value = $this->numberize($value);
                break;
            case AkeneoHeaderTypes::SELECT:
                // TODO implement attributes reader
                if ($this->getOption('filter_option_codes')) {
                    $value = $this->filterOptionCode($attributeCode, $value);
                }
                break;
            case AkeneoHeaderTypes::MULTISELECT:
                // TODO implement attributes reader
                if (is_array($value) && $this->getOption('filter_option_codes')) {
                    $value = $this->filterOptionCodes($attributeCode, $value);
                }
                if (is_string($value)) {
                    $value = [$value];
                }
                break;
            case AkeneoHeaderTypes::PRICE:
                $amount = null;
                $unit = $this->getOption('default_currency');
                if (is_numeric($value)) {
                    $amount = $this->numberize($value);
                }
                if (is_array($value)) {
                    if (array_key_exists('amount', $value)) {
                        $amount = $value['amount'];
                    }
                    if (array_key_exists('currency', $value)) {
                        $unit = $value['currency'];
                    }
                }

                $value = [[
                    'amount' => $amount,
                    'currency' => $unit,
                ]];
                break;
            case AkeneoHeaderTypes::METRIC:
                $amount = null;
                $unit = $this->getOption('default_metrics:list')[$attributeCode] ?? null;
                if (is_numeric($value)) {
                    $amount = $this->numberize($value);
                }
                if (is_array($value)) {
                    if (array_key_exists('amount', $value)) {
                        $amount = $value['amount'];
                    }
                    if (array_key_exists('unit', $value)) {
                        $unit = $value['unit'];
                    }
                }

                $value = [
                    'amount' => $amount,
                    'unit' => $unit,
                ];
                break;
            case AkeneoHeaderTypes::REFDATA_MULTISELECT:
                if (is_string($value)) {
                    $value = [$value];
                }
                break;
            case AkeneoHeaderTypes::REFDATA_SELECT:
                // no changes
                break;
        }

        return [
            'locale' => $localizable ? $context['locale'] ?? $this->getOption('default_locale') : null,
            'scope' => $scopable ? $context['scope'] ?? $this->getOption('default_scope') : null,
            'data' => $value,
        ];
    }

    private function numberize($value)
    {
        if (is_float($value)) {
            return $value;
        }
        if (is_integer($value)) {
            return $value;
        }
        if (is_string($value)) {
            $posNum = str_replace(',', '.', $value);
            return is_numeric($posNum) ? $posNum: $value;
        }
    }

    public function filterOptionCode(string $attributeCode, string $value)
    {
        return  str_replace('-', '_', str_replace(' ', '-', $value));
    }

    public function filterOptionCodes(string $attributeCode, $optionValues)
    {
        return array_map(function ($optionValue) {
            return str_replace('-', '_', str_replace(' ', '-', $optionValue['code']));
        }, $optionValues);
    }

    /**
     * This function return the option_code that was made earlier
     * When generating option codes we expect a full export strategy
     */
    public function findAttributeOptionCode(string $attributeCode, string $optionLabel)
    {
        return $this->getReader()->find([
            'attribute' => $attributeCode,
            $this->getOption('option_label') => $optionLabel]
        )->getIterator()->current()['code'];
    }

    /**
     * This function will extract attribute values from the item based on the attribute:list
     */
    public function getProductValues(array $item): \Generator
    {
        foreach ($this->getOption('attributes:list') ?? [] as $attributeCode) {
            if (array_key_exists($attributeCode, $item)) {
                yield $attributeCode => $item[$attributeCode];
            }
        }
    }

    public function revert(array $item): array
    {
        $container = $this->getOption('container');

        $output = [];
        $output['sku'] = $item['sku'];
        if (array_key_exists('family', $item)) {
            $output['family'] = $item['family'];
        }

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

    public function getName(): string
    {
        return 'flat/akeneo/product/csv';
    }
}