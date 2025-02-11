<?php

namespace Misery\Component\Common\Replacer;

use Misery\Component\Common\Picker\StandardValuePicker;

class StandardValueReplacer
{
    public static function replace(array $item, string $key, callable $replace, array $context = [])
    {
        foreach (StandardValuePicker::pick($item, $key, $context) as $index => $itemValue) {
            $item['values'][$key][$index]['data'] = $replace($itemValue['data']);
        }

        return $item;
    }
}