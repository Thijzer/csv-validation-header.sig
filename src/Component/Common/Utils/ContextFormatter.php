<?php

namespace Misery\Component\Common\Utils;

class ContextFormatter
{
    public static function format(array $context, array $data): array
    {
        $replacements = [];
        foreach ($context as $key => $contextValue) {
            $replacements["%$key%"] = $contextValue;
        }

        $newData = [];
        foreach ($data as $key => &$value) {
            if (is_string($key)) {
                $newKey = strtr($key, $replacements);
                $newData[$newKey] = $value;
            } else {
                $newData[$key] = $value;
            }
        }

        foreach ($newData as &$value) {
            if (is_array($value)) {
                $value = self::format($context, $value);
            } elseif (is_string($value)) {
                $value = strtr($value, $replacements);
            }
        }

        return $newData;
    }
}
