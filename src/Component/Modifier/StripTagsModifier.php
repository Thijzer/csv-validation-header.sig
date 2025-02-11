<?php

namespace Misery\Component\Modifier;

use Misery\Component\Common\Modifier\CellModifier;

class StripTagsModifier implements CellModifier
{
    public const NAME = 'strip_tags';

    /**
     *
     * @param string $value
     * @return string
     */
    public function modify(string $value): string
    {
        return strip_tags($value);
    }
}