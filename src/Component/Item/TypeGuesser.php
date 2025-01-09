<?php

namespace Misery\Component\Item;

use Misery\Component\Converter\Matcher;

class TypeGuesser
{
    public static function guess(array $item): array
    {
        $set = [];
        foreach ($item as $key => $value) {
            /** @var $matcher Matcher */
            if (is_array($value) && array_key_exists('matcher', $value)) {
                $matcher = $value['matcher'];
                $set[$matcher->getMainKey()] = $value['data'];
                continue;
            }

            $filtered = is_array($value) ? array_diff($value, ['', '-']) : null;
            switch (true) {
                case is_numeric($value):
                    $type = 'numeric_value '.$value;
                    break;
                case is_float($value):
                    $type = 'float_value '.$value;
                    break;
                case $filtered && count($filtered) === count(array_filter($filtered,function ($value) {
                        return is_numeric(str_replace(['.', ','], '', $value));
                    })):
                    $type = 'numeric_list:'. count($item);
                    break;
                case $filtered && count($filtered) === count(array_filter($filtered,'is_string')):
                    $type = 'string_list:'. count($item);
                    break;
                case $filtered === [] && $item !== []:
                    $type = 'empty_values_list';
                    break;
                case $filtered === []:
                    $type = 'empty_array';
                    break;
                case $value === '':
                    $type = 'empty_string';
                    break;
                case is_string($value):
                    $type = 'string_value '.$value;
                    break;
                case is_array($value):
                    $type = 'array';
                    break;
                default:
                    $type = 'unknown';
            }
            $set[$key] = $type;
        }

        return $set;
    }
}