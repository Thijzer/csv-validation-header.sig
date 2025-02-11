<?php

namespace Misery\Component\Converter;

use Misery\Component\Akeneo\Header\AkeneoHeaderTypes;
use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Reader\ItemCollection;

/**
 * This Converter converts flat product to std data
 * We focus on correcting with minimal issues
 * The better the input you give the better the output
 */
class AkeneoFlatAttributeOptionsToCsv implements ConverterInterface, ItemCollectionLoaderInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private array $index = [];
    private $options = [
        'attribute_types:list' => [],
        'option_label_locales' => [],
        'option_label_field' => 'code',
        'option_field' => 'code',
    ];

    public function load(array $item): ItemCollection
    {
        return new ItemCollection($this->convert($item));
    }

    public function convert(array $item): array
    {
        $result = [];
        foreach ($item as $attributeCode => $itemValue) {
            $valueSet = $this->getAkeneoDataStructure($attributeCode, $itemValue);
            if (empty($valueSet)) {
                continue;
            }
            foreach ($valueSet as $value) {
                $result[] = $value;
            }
        }

        return $result;
    }

    public function getAkeneoDataStructure(string $attributeCode, $value): array
    {
        $options = [];

        $type = $this->getOption('attribute_types:list')[$attributeCode] ?? null;
        if (null === $type) {
            return [];
        }

        switch ($type) {
            case AkeneoHeaderTypes::SELECT:
                if (!empty($value)) {
                    $options[] = $this->assembleOptionValue($value, $attributeCode);
                }

                break;
            case AkeneoHeaderTypes::MULTISELECT:
                if (is_array($value)) {
                    foreach ($value as $valueSet) {
                        $option = $this->assembleOptionValueFromOptionField($valueSet, $attributeCode);
                        if ($option && !in_array($option['id'], $this->index)) {
                            $this->index[$option['id']] = null;
                            $options[] = $option;
                        }
                    }
                }
                break;
            default:
                return [];
        }

        return $options;
    }

    private function assembleOptionValue(string $optionValue, string $attributeCode): null|array
    {
        $locales = $this->getOption('option_label_locales');

        $labels = [];
        foreach ($locales as $locale) {
            $labels[$locale] = $optionValue;
        }

        return $this->generateOption(
            $optionValue,
            $attributeCode,
            $labels
        );
    }

    private function assembleOptionValueFromOptionField(array $valueSet, string $attributeCode): null|array
    {
        $locales = $this->getOption('option_label_locales');
        $labelField = $this->getOption('option_label_field');

        $optionValue = $valueSet[$this->getOption('option_field')] ?? null;
        if (!$optionValue) {
            return null;
        }
        $labels = [];
        if (isset($valueSet[$labelField])) {
            foreach ($locales as $locale) {
                $labels[$locale] = $valueSet[$labelField];
            }
        }

        return $this->generateOption(
            $optionValue,
            $attributeCode,
            $labels
        );
    }

    private function generateOption(string $code, string $attributeCode, array $labels): array
    {
        return ArrayFunctions::flatten([
            'id' => "$code/$attributeCode",
            'code' =>  str_replace('-', '_', str_replace(' ', '-', $code)),
            'attribute' => $attributeCode,
            'sort_order' => count($this->index),
            'label' => $labels,
        ], '-');
    }

    public function revert(array $item): array
    {
        return ArrayFunctions::flatten($item, '-');
    }

    public function getName(): string
    {
        return 'flat/akeneo/attribute_option/csv';
    }
}