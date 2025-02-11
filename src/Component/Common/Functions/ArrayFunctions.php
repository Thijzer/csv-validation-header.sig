<?php

namespace Misery\Component\Common\Functions;

class ArrayFunctions
{
    /**
     * ISSUE with unflatten
     * label-23 => becomes label[23]
     * but label => resets label[] via overwriting.
     *
     * Reverse flatten an associative array to multidimensional one
     * by separating keys on separator.
     *
     * @param array $array
     * @param string $separator
     *
     * @return array
     */
    public static function unflatten(array $array, $separator = '.')
    {
        $output = [];
        foreach ($array as $key => $value) {
            static::array_set($output, $key, $value, $separator);
            if (\is_array($value) && \strpos($key, $separator) !== false) {
                $output[$key] = static::unflatten($value, $separator);
            }
        }

        foreach (\array_keys($output) as $key) {
            if (\array_key_exists($key, $array) && \is_array($output[$key])) {
                $output[$key] = ['' => $array[$key]] + $output[$key];
            }
        }

        return $output;
    }

    /**
     * Flattens an multi-dimensional array to associative array
     * by adding combining the keys with a prefix.
     *
     * @param array $array
     * @param string $prefix
     * @param mixed $separator
     * @param array $targetList
     * @param bool $preserveIntKeys
     *
     * @return array
     */
    public static function flatten(array $array, string $separator = '.', string $prefix = '', array $targetList = [], bool $preserveIntKeys = false)
    {
        $result = [];
        foreach ($array as $key => $value) {
            // If whitelist is not empty and key is not in whitelist, skip this key
            if (!empty($targetList) && !in_array($key, $targetList, true)) {
                $result[$key] = $value;
                continue;
            }

            // If the key is an integer and preserveIntKeys is true, do not flatten this part of the array
            if ($preserveIntKeys && is_int($key)) {
                $result[rtrim($prefix, $separator)][$key] = $value;
                //continue; // Skip the rest of the logic for this key
            }

            if (is_array($value) && $value !== []) {
                $result += static::flatten($value, $separator, $prefix . $key . $separator, [], $preserveIntKeys);
                continue;
            }

            $finalKey = $key === '' ? rtrim($prefix, $separator) : $prefix . $key;
            $result[$finalKey] = $value;
        }

        return $result;
    }

    public static function merge(array $a, array $b)
    {
        return static::unflatten(array_merge(static::flatten($a), static::flatten($b)));
    }

    public static function multiCompare(array $a, array $b): array
    {
        $a = static::flatten($a);
        $b = static::flatten($b);

        return static::unflatten(array_diff($b, $a));
    }

    /**
     * Normalizes array keys to integers.
     *
     * @param array $param
     *
     * @return array
     */
    public static function normalizeKeys(array $param)
    {
        $keys = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveArrayIterator($param)) as $key => $val) {
            $keys[$key] = '';
        }

        $data = [];
        foreach ($param as $values) {
            $data[] = array_merge($keys, $values);
        }

        return $data;
    }

    /**
     * Merge Array A and B together as One
     * Some new keys will be generated to avoid collision with same values
     * Array A is leading in key-order
     * Array B has priority on values
     *
     * @param array $a
     * @param array $b
     *
     * @return array
     */
    public static function arrayUnion(array $a, array $b)
    {
        return array_merge(
            array_intersect($a, $b),
            array_diff($a, $b),
            array_diff($b, $a)
        );
    }

    public static function arrayDiff(array $a, array $b): array
    {
        $intersect = array_intersect($a, $b);

        return array_merge(array_diff($a, $intersect), array_diff($b, $intersect));
    }

    /**
     * this array_combine version will also combine
     * when the elements form array $b are less then the values of array $a
     */
    public static function arrayCombine(array $a, array $b)
    {
        return array_combine($a, $b+array_fill(0,count($a),null));
    }

    /**
     * @param mixed $haystack
     * @param array $needles
     *
     * @return bool
     */
    public static function strpos_array($haystack, $needles = []): bool
    {
        return $haystack !== str_replace($needles, '', $haystack);
    }

    public static function array_set(&$array, $key, $value, $prefix = '.'): array
    {
        if (null === $key) {
            return $array = $value;
        }

        $keys = \explode($prefix, $key);

        while (\count($keys) > 1) {
            $key = \array_shift($keys);
            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !\is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[\array_shift($keys)] = $value;

        return $array;
    }

    /** function array_filter_recursive
     *
     *      Exactly the same as array_filter except this function
     *      filters within multi-dimensional arrays
     *
     * @param array $array
     * @param callable $callback optional callback function name
     *
     * @return array merged array
     */
    public static function array_filter_recursive(array $array, callable $callback): array
    {
        $array = array_map(static function($item) use ($callback) {
            return is_array($item) ? static::array_filter_recursive($item, $callback) : $item;
        }, $array);

        return array_filter($array, static function($item) use ($callback) {
            return $callback($item);
        });
    }

    public static function fill_with_empty(array $array): array
    {
        return array_fill_keys($array, null);
    }

    public static function array_map_recursive(array $array, callable $callback): array
    {
        $array = array_map(static function($item) use ($callback) {
            return is_array($item) ? static::array_map_recursive($item, $callback) : $item;
        }, $array);

        return array_map(static function($item) use ($callback) {
            return $callback($item);
        }, $array);
    }
}
