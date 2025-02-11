<?php

namespace Misery\Component\Common\Utils;

class ValueFormatter
{
    /**
     * format('%amount% %unit%', ['amount' => 1, 'unit' => 'GRAM']);
     * returns '1 GRAM'
     *
     * @param array $values
     * @param string $format
     *
     * @return string
     */
    public static function format(string $format, array $values): string
    {
        $replacements = [];
        foreach ($values as $key => $value) {
            if (!is_array($value) && $value !== null && str_contains($format, "%$key%")) {
                $replacements["%$key%"] = $value;
            }
        }

        return strtr($format, $replacements);
    }

    public static function getKeys(string $format): array
    {
        preg_match_all('/%([a-zA-Z0-9_]+)%/', $format, $matches);
        return $matches[1] ?? [];
    }

    public static function recursiveFormat(string $format, array $values): string
    {
        foreach ($values as $value) {
            if (is_array($value)) {
                $format = self::recursiveFormat($format, $value);
            }
        }

        return self::format($format, $values);
    }

    public static function formatMulti(array $formats, array $values): array
    {
        foreach ($formats as &$format) {
            $format = static::format($format, $values);
        }

        return $formats;
    }
}