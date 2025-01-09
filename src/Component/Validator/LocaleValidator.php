<?php

namespace Misery\Component\Validator;

class LocaleValidator
{
    public static function validate(string $value): bool
    {
        return strlen($value) === 5 && ($value[2] === '_') &&
            ($value[0].$value[1] === strtolower($value[0].$value[1])) &&
            ($value[3].$value[4] === strtoupper($value[3].$value[4]));
    }
}