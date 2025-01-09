<?php

namespace Misery\Component\Modifier;

use Induxx\Component\HotFolder\Finder\FileNameValidator;
use Misery\Component\Common\Modifier\CellModifier;
use Misery\Component\Common\Sanitizer\FileNameSanitizer;

/**
 * Class SnakeCaseModifier
 * @package Misery\Component\Modifier
 *
 * value response may contain only letters, numbers and underscores
 * value is unrecoverable after modification
 */
class SnakeCaseModifier implements CellModifier
{
    public const NAME = 'snake_case';

    /**
     *
     * @param string $value
     * @return string
     */
    public function modify(string $value): string
    {
        $delimiter = '_';

        if (false === ctype_lower($value)) {
            $value = (string) \preg_replace('/\s+/u', '', \ucwords($value));
            $value = (string) \mb_strtolower(\preg_replace(
                    '/(.)(?=[A-Z])/u',
                    '$1' . $delimiter,
                    $value
                ) ?? '');

        }

        return $value;
    }
}