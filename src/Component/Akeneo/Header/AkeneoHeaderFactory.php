<?php

namespace Misery\Component\Akeneo\Header;

class AkeneoHeaderFactory
{
    public static function create(
        array $attributeTypes,
        array $localizableCodes,
        array $scopableCodes,
        array $locales,
        array $scopes,
        array $currencies
    ): AkeneoHeader {
        $header = new AkeneoHeader([
            'locale_codes' => $locales,
            'scope_codes' => $scopes,
            'currencies' => $currencies,
        ]);
        $header->addValue('sku', 'pim_catalog_identifier');

        foreach ($attributeTypes as $attributeCode => $attributeType) {
            $header->addValue($attributeCode, $attributeType, ['is_product_value' => true]);
            if (in_array($attributeCode, $localizableCodes)) {
                $header->addValue($attributeCode, $attributeType, ['has_locale' => true]);
            }
            if (in_array($attributeCode, $scopableCodes)) {
                $header->addValue($attributeCode, $attributeType, ['has_scope' => true]);
            }
        }

        return $header;
    }
}