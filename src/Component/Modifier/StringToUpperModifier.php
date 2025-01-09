<?php

namespace Misery\Component\Modifier;

use Misery\Component\Common\Modifier\CellModifier;

class StringToUpperModifier implements CellModifier
{
    public const NAME = 'upper';

    /**
     *
     * @param string $value
     * @return string
     */
    public function modify(string $value): string
    {
        return strtoupper($value);
    }
}