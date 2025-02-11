<?php

namespace Misery\Component\Modifier;

use Misery\Component\Common\Modifier\CellModifier;

/**
 * Class ReferenceCodeModifier
 * @package Misery\Component\Modifier
 *
 * value response may contain only letters, numbers and underscores
 * value is unrecoverable after modification
 */
class ReferenceCodeModifier implements CellModifier
{
    public const NAME = 'reference_code';

    /**
     *
     * @param string $value
     * @return string
     */
    public function modify(string $value): string
    {
        $delimiter = '_';

        if (false === ctype_lower($value)) {
            $value = (string) \preg_replace('/[^a-zA-Z0-9_]/',$delimiter, $value);
            $value = trim($value, $delimiter);
        }

        return $value;
    }
}