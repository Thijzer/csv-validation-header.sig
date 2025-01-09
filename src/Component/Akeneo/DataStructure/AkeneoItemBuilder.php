<?php

namespace Misery\Component\Akeneo\DataStructure;

use Assert\Assert;
use Misery\Component\Converter\Matcher;
use Misery\Model\DataStructure\Item;
use Misery\Model\DataStructure\ItemInterface;

class AkeneoItemBuilder
{
    /**
     * Convert multi-dimensional structure data to a Single Dimensional Structure with Matcher
     *
     * Product Payload
     *
     * {
     *   "values": [
     *     "color": [
     *       {
     *         "data": "red",
     *         "locale": null,
     *         "scope": null,
     *       }
     *     ]
     *   ]
     * }
     *
     * Becomes
     *
     * {
     *   "values|color": {
     *         "data": "red",
     *         "locale": null,
     *         "scope": null,
     *         "matcher": {"sep":"|", "matches":["values", "color"], "scope":null, "locale":null}
     *    }
     * }
     *  - Scope-able & localizable: "values|color|nl_BE|ecom"
     *  - Scope-able: "values|color|ecom"
     *  - localizable: "values|color|nl_BE"
     **/
    public static function fromProductApiPayload(array $productData, array $context = []): ItemInterface
    {
        $itemObj = new Item('product');
        $attributeData = $context['attribute_types'] ?? [];
        unset($context['attribute_types']);

        Assert::that($productData)
            ->isArray()
            ->keyExists('values')
        ;

        foreach ($productData as $property => $propertyValue) {
            if ($property === 'values') {
                continue;
            }
            if (is_array($propertyValue)) {
                $propertyValue['matcher'] = Matcher::create($property);
            }
            $itemObj->addItem($property, $propertyValue);
        }

        // convert every value into an ItemNode
        foreach ($productData['values'] as $attributeCode => $productValues) {
            foreach ($productValues as $productValue) {
                $matcher = Matcher::create('values|'.$attributeCode, $productValue['locale'], $productValue['scope']);
                // don't overwrite a type when it has been given
                $productValue['type'] = $productValue['type'] ?? $attributeData[$matcher->getPrimaryKey()] ?? null;
                $productValue['matcher'] = $matcher;

                $itemObj->addItem($matcher->getMainKey(), $productValue, $context);
            }
        }

        return $itemObj;
    }

    public static function fromCatalogApiPayload(array $catalogData, array $context = []): ItemInterface
    {
        Assert::that($context)->keyExists('class', 'You need to supply a class');

        $itemObj = new Item($context['class']);

        Assert::that($catalogData)
            ->isArray()
            ->keyExists('code')
        ;

        foreach ($catalogData as $property => $propertyValue) {
            if ($property === 'labels') {
                foreach ($propertyValue as $locale => $labelValue) {
                    $value['matcher'] = $m = Matcher::create($property, $locale);
                    $value['data'] = $labelValue;
                    $itemObj->addItem($m->getMainKey(), $value);
                }
                continue;
            }
            if (is_array($propertyValue)) {
                $propertyValue['matcher'] = Matcher::create($property);
            }
            $itemObj->addItem($property, $propertyValue);
        }

        return $itemObj;
    }
}
