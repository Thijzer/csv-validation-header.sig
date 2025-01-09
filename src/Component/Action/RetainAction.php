<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Model\DataStructure\ItemInterface;

class RetainAction implements OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'retain';

    /** @var array */
    private $options = [
        'keys' => [],
        'mode' => 'multi',
    ];

    /**
     * This mode only applies for single dimensional arrays
     */
    private function applyForSingleDimensionItem(array $item): array
    {
        $keys = array_intersect($this->options['keys'], array_keys($item));
        if (empty($keys)) {
            return $item;
        }

        $tmp = [];
        foreach ($keys as $key) {
            $tmp[$key] = $item[$key];
        }

        return $tmp;
    }

    public function applyAsItem(ItemInterface $item): ItemInterface
    {
        $fields = $this->getOption('keys', $this->getOption('fields'));
        if (empty($fields)) {
            return $item;
        }

        $itemCodesToRemove = array_diff($item->getItemCodes(), $fields);
        foreach ($itemCodesToRemove as $itemCode) {
            $item->removeItem($itemCode);
        }

        return $item;
    }

    /**
     * The default apply will look into for multi dimensional array
     */
    public function apply(array $item): array
    {
        if ($this->getOption('mode') === 'single') {
            return $this->applyForSingleDimensionItem($item);
        }

        $optionsToKeep = $this->options['keys'];

        // we loop all configured option values
        $valuesToKeep = $this->getNestedValuesToKeep($optionsToKeep);

        // our item could be a flat array or nested array
        // we need to check the item for both
        // for example the labels['nl_BE'] or the labels-nl_BE index
        $optionsToKeep = array_merge($optionsToKeep, array_keys($valuesToKeep));
        $keys = array_intersect($optionsToKeep, array_keys($item));

        // if we don't find any key to keep, return the original item
        if (empty($keys)) {
            return $item;
        }

        // start building a new item array
        $newItem = [];
        foreach ($keys as $fieldKey) {
            // first we check if the field we are working with is a flat value
            if (isset($item[$fieldKey]) && !isset($valuesToKeep[$fieldKey])) {
                $newItem[$fieldKey] = $item[$fieldKey];

                continue;
            }

            // we then check if the field we are working with is kept as a value array
            // for example $item['labels']
            if (isset($valuesToKeep[$fieldKey])) {
                foreach ($valuesToKeep[$fieldKey] as $indexCode) {
                    if (!isset($item[$fieldKey][$indexCode])) {
                        continue;
                    }

                    // nested array value isset. Flatten it.
                    $newItem[$fieldKey . '-' . $indexCode] = $item[$fieldKey][$indexCode];
                }
            }
        }

        return $newItem;
    }

    private function getNestedValuesToKeep(array $fieldsToKeep): array
    {
        $valuesToKeep = [];
        foreach ($fieldsToKeep as $value) {
            if (strpos($value, '-') === false) {
                continue;
            }

            // Akeneo specific - we found a dash value. This value is scopable or localizable
            // we explode the value on dash
            $value = explode('-', $value);

            // index 0 should be the Akeneo attribute code
            if (!isset($valuesToKeep[$value[0]])) {
                $valuesToKeep[$value[0]] = [];
            }

            // add our current locale to our array
            $valuesToKeep[$value[0]][] = $value[1];
        }

        return $valuesToKeep;
    }
}