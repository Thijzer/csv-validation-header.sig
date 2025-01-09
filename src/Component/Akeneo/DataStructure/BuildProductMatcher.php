<?php

namespace Misery\Component\Akeneo\DataStructure;

use Misery\Component\Converter\Matcher;
use Misery\Model\DataStructure\ItemInterface;

class BuildProductMatcher
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
    public static function fromApiPayload(array $productData, array $attributeData = []): array
    {
        throw new \Exception('Deprecated! use a proper Factory');
    }

    public static function fromItem(ItemInterface $item): array
    {
        $fields = [];
        foreach ($item->getItemNodes() as $code => $itemNode) {
            $matcher = $itemNode->getMatcher();

            if ($matcher->matches('values')) {
                $fields['values'][$matcher->getPrimaryKey()][] = $itemNode->getValue();
            } else {
                $fields[$code] = $itemNode->getValue();
            }
        }

        return $fields;
    }

    public static function revertMatcherToProduct(array $productData): array
    {
        $fields = [];
        foreach ($productData as $field => &$fieldValue) {
            $matcher = $fieldValue['matcher'] ?? null;
            if ($matcher instanceof Matcher) {
                unset($fieldValue['matcher']);
                $fields['values'][$matcher->getPrimaryKey()][] = $fieldValue;
            } else {
                $fields[$field] = $fieldValue;
            }
        }

        return $fields;
    }

    public static function revertToKeyValue(array $productData): array
    {
        $fields = [];
        foreach ($productData as $field => $fieldValue) {
            $fields[$field] = $fieldValue;
            $matcher = $fieldValue['matcher'] ?? null;
            if ($matcher instanceof Matcher) {
                $fields[$field] = $fieldValue['data'] ?? null;
            }
        }

        return $fields;
    }
}