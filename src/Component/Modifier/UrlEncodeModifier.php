<?php

namespace Misery\Component\Modifier;

use Misery\Component\Common\Modifier\CellModifier;

class UrlEncodeModifier implements CellModifier
{
    public const NAME = 'url_encode';

    /**
     *
     * @param string $value
     * @return string
     */
    public function modify(string $value): string
    {
        return rawurlencode($value);
    }
}