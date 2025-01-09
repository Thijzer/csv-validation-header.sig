<?php

namespace Misery\Component\Statement;

class ItemPlaceholder
{
    public static function matches(string $value): bool
    {
        return str_starts_with($value, '{') && str_ends_with($value, '}');
    }

    public static function extract(string $value): string
    {
        return rtrim(ltrim($value, '{'), '}');
    }

    /** Exact match "{test}" */
    public static function replace(string $text, array $item): array|string|null
    {
        return $item[self::extract($text)] ?? self::textReplace($text, $item) ?? $text;
    }

    /** Exact match in text "This is a {test}" (slower) */
    public static function textReplace(string $text, array $item): array|string|null
    {
        return preg_replace_callback('/\{([a-zA-Z0-9.-_]+)\}/', function ($matches) use ($item) {
            // $matches[1] is the key inside the brackets
            return $item[$matches[1]] ?? $matches[0];
        }, $text);
    }
}