<?php

namespace Misery\Component\Action;

use Misery\Component\AttributeFormatter\AttributeValueFormatter;
use Misery\Component\AttributeFormatter\BooleanLabelsAttributeFormatter;
use Misery\Component\AttributeFormatter\MetricAttributeFormatter;
use Misery\Component\AttributeFormatter\NumberAttributeFormatter;
use Misery\Component\AttributeFormatter\PriceCollectionFormatter;
use Misery\Component\AttributeFormatter\PropertyFormatterRegistry;
use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Converter\Matcher;

class ConvergenceAction implements OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'convergence';

    /** @var array */
    private $options = [
        'store_field' => null,
        'fields' => [],
        'list' => null,
        'item_sep' => ', ',
        'key_value_sep' => ': ',
        'encapsulate' => false,
        'encapsulation_char' => '"',
    ];

    public function apply(array $item): array
    {
        $field = $this->getOption('store_field');
        if (null === $field) {
            return $item;
        }

        $fields = $this->getOption('list', $this->getOption('fields'));

        $keyValueSeparator = $this->getOption('key_value_sep');
        $elementSeparator = $this->getOption('item_sep');
        $encapsulate = $this->getOption('encapsulate');
        $encapsulationChar = $this->getOption('encapsulation_char');

        $result = [];
        foreach ($fields as $fieldKey) {
            // converted data
            $key = $this->findMatchedValueData($item, $fieldKey);
            $value = $item[$fieldKey]['data'] ?? $item[$fieldKey] ?? $item[$key]['data'] ?? null;

            // Include only the fields with assigned values
            // only for string data types
            if (is_string($value)) {
                // Encapsulate keys and values if needed
                $fieldKey = $encapsulate ? $encapsulationChar . $fieldKey . $encapsulationChar : $fieldKey;
                $value = $encapsulate ? $encapsulationChar . $value . $encapsulationChar : $value;
                $result[] = $fieldKey . $keyValueSeparator . $value;
            }
        }

        $item[$field] = implode($elementSeparator, $result);

        return $item;
    }

    private function findMatchedValueData(array $item, string $field): int|string|null
    {
        foreach ($item as $key => $itemValue) {
            $matcher = $itemValue['matcher'] ?? null;
            /** @var $matcher Matcher */
            if ($matcher && $matcher->matches($field)) {
                return $key;
            }
        }

        return null;
    }
}