<?php

namespace Misery\Component\Converter;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Reader\ItemCollection;

class GenerateLineageCategoriesConverter implements ConverterInterface, ItemCollectionLoaderInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;
    private array $collectedIds = [];

    private $options = [
        'prefix' => null,
        'master' => null,
        'lineage_separator' => '|',
    ];

    public function load(array $item): ItemCollection
    {
        return new ItemCollection($this->convert($item));
    }

    /**
     * This script processes a JSON object representing a category item,
     * generates its root and subcategories based on its lineage, and
     * outputs the categories in reverse order.
     *
     * Given a JSON object with `code`, `parent`, and `lineage` properties,
     * it will create hierarchical categories and sort them from the most
     * general category to the most specific one.
     *
     * option.prefix: purchase_
     *
     * Example Input JSON: (we build this from our product DataSet)
     * {"code":"purchase_women_clothing_bottoms_leggings_legging_long","parent":"purchase_women_clothing_bottoms_leggings","lineage":"women|clothing|bottoms|leggings"}
     *
     * Example Output:
     * code: purchase_women, parent: null |ROOT
     * code: purchase_women_clothing, parent: purchase_women
     * code: purchase_women_clothing_bottoms, parent: purchase_women_clothing
     * code: purchase_women_clothing_bottoms_leggings, parent: purchase_women_clothing_bottoms
     * code: purchase_women_clothing_bottoms_leggings_legging_long, parent: purchase_women_clothing_bottoms_leggings
     *
     * Define another ROOT category
     * option.master: purchase
     * code: purchase_women, parent: purchase | NEW_ROOT
     *
     */
    public function convert(array $item): array
    {
        // Extract the code, parent, and lineage
        $code = $item['code'];
        $parent = $item['parent'];
        $lineage = $item['lineage'];
        $lineageSeparator = $this->getOption('lineage_separator');

        // Add the 'purchase_' prefix
        $prefix = $this->getOption('prefix');
        // set a master tree
        $master = $this->getOption('master');

        // Explode the lineage into an array
        $lineageArray = explode($lineageSeparator, $lineage);

        // Initialize an empty array to store the results
        $categories = [];

        // Add the original item to the categories array
        $categories[] = ['code' => $code, 'parent' => $parent];

        // Generate the categories from the lineage
        for ($i = count($lineageArray) - 1; $i >= 0; $i--) {
            // Create the current category code
            $currentCode = $prefix . implode('_', array_slice($lineageArray, 0, $i + 1));

            // Create the parent category code
            $parentCode = $i > 0 ? $prefix . implode('_', array_slice($lineageArray, 0, $i)) : null;

            // Define another ROOT category
            if ($parentCode === null && $master) {
                $parentCode = $master;
            }

            // Add the current category to the categories array
            $categories[] = ['code' => $currentCode, 'parent' => $parentCode];
        }

        return array_reverse($categories);
    }

    public function revert(array $item): array
    {
        return $item;
    }

    public function getName(): string
    {
        return 'lineage/categories/api';
    }
}