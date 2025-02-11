<?php

namespace Misery\Component\Modifier;

use Misery\Component\Common\Modifier\CellModifier;

class StringToLowerModifier implements CellModifier
{
    public const NAME = 'lower';

    /**
     *
     * @param string $value
     * @return string
     */
    public function modify(string $value): string
    {
        return strtolower($value);
    }
}