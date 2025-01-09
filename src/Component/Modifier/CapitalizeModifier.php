<?php

namespace Misery\Component\Modifier;

use Misery\Component\Common\Modifier\CellModifier;

class CapitalizeModifier implements CellModifier
{
    public const NAME = 'capitalize';

    /**
     * @param string $value
     * @return string
     */
    public function modify(string $value): string
    {
        return ucfirst(strtolower($value));
    }
}