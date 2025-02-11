<?php

namespace Misery\Component\Converter\Akeneo;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Converter\ConverterInterface;
use Misery\Component\Converter\ItemCollectionLoaderInterface;
use Misery\Component\Filter\ColumnReducer;
use Misery\Component\Modifier\ReferenceCodeModifier;
use Misery\Component\Reader\ItemCollection;

/**
 * This case is not ready for scope-able options, nor API options
 * This Converter extracts the options from the Akeneo Product data structure
 */
class AkeneoOptionExtractor implements ConverterInterface, ItemCollectionLoaderInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private $options = [
        'attribute_option_codes:list' => [],
        'parent_identifier_field' => 'attribute',
        'identifier_field' => 'code',
        'reference_field' => 'reference',
        'reference_code' => true, # force the option code to be a reference-able code
        'lower_cased' => true, # force the option code to be lower cased
    ];

    public function convert(array $item): array
    {
        $identifierField = $this->getOption('identifier_field');
        $parentIdentifierField = $this->getOption('parent_identifier_field');
        $referenceField = $this->getOption('reference_field');
        $optionCodes = $this->getOption('attribute_option_codes:list');
        $referenceCode = $this->getOption('reference_code');
        $lowerCased = $this->getOption('lower_cased');

        // Reduce the item to only the options
        $item = ColumnReducer::reduceItem($item, ...$optionCodes);

        // Remove null values
        $item = array_filter($item);

        $result = [];
        if ($item === []) {
            return $result;
        }

        // Create the options structure
        foreach ($item as $key => $value) {
            if (empty($value)) {
                continue;
            }
            if (is_array($value)) {
                foreach ($value as $option) {
                    $optionCode = $this->renderCode($option, $referenceCode, $lowerCased);
                    $id = $optionCode . '-'. $key;
                    $result[$id][$parentIdentifierField] = $key;
                    $result[$id][$identifierField] = $optionCode;
                    $result[$id][$referenceField] = $id;
                }
            } else {
                $optionCode = $this->renderCode($value, $referenceCode, $lowerCased);
                $id = $optionCode . '-'. $key;
                $result[$id][$parentIdentifierField] = $key;
                $result[$id][$identifierField] = $optionCode;
                $result[$id][$referenceField] = $id;
            }
        }

        return $result;
    }

    /**
     * Render the option code.
     * This method is used to render the option code into a acceptable format for akeneo.
     *
     * @param string $value The value to render.
     * @param bool $referenceCode Whether to use reference code.
     * @param bool $lowerCase Whether to convert to lower case.
     * @return string The rendered code.
     */
    private function renderCode(string $value, bool $referenceCode = true, bool $lowerCase = true): string
    {
        if ($referenceCode) {
            $value = (new ReferenceCodeModifier())->modify($value);
        }
        if ($lowerCase) {
            $value = strtolower($value);
        }

        return $value;
    }

    /**
     * Load the given item array as an ItemCollection, exploding the item into multiple loop-able items.
     *
     * @param array $item The item to load.
     * @return ItemCollection The loaded item collection.
     */
    public function load(array $item): ItemCollection
    {
        return new ItemCollection($this->convert($item));
    }

    public function getName(): string
    {
        return 'akeneo/option/extractor';
    }

    public function revert(array $item): array
    {
        return $item;
    }
}