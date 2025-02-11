<?php

namespace Misery\Component\Converter;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Reader\ItemCollection;

/**
 * This Converter converts a flat Category
 */
class BCCategoriesApiConverter implements ConverterInterface, ItemCollectionLoaderInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;
    private array $collectedIds = [];

    public function load(array $item): ItemCollection
    {
        return new ItemCollection($this->convert($item));
    }

    public function convert(array $item): array
    {
        $categories = [];
        foreach ($item['itemCategories'] as $categoryItem) {
            if (!empty($categoryItem['parentCategory'])) {
                $categories[] = [
                    'code' => $categoryItem['parentCategory'],
                    'parent' => '',
                ];
            }

            $categories[] = [
                'code' => $categoryItem['code'],
                'parent' => $categoryItem['parentCategory'],
            ];
        }

        return $categories;
    }

    public function revert(array $item): array
    {
        return $item;
    }

    public function getName(): string
    {
        return 'bc/categories/api';
    }
}