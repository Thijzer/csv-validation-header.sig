<?php

namespace Misery\Component\Converter;

class AS400HeaderContext
{
    public function create(array $attributes, array $locales): array
    {
        $output['sku'] = null;

        foreach ($attributes as $attribute) {
            foreach ($locales as $locale) {
                $output[implode('-', [$attribute, $locale])] = null;
            }
        }

        return $output;
    }
}