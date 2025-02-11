<?php

namespace Misery\Component\Common\Sanitizer;

class FileNameSanitizer
{
    public static function sanitize(string $value, string $delimiter = ''): string
    {
        return (string) \preg_replace('/[^a-zA-Z0-9\-\._]/',$delimiter, $value);
    }
}