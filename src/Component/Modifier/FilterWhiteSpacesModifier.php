<?php

namespace Misery\Component\Modifier;

use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Common\Modifier\RowModifier;

class FilterWhiteSpacesModifier implements RowModifier
{
    public const NAME = 'filter_whitespace';

    public function modify($value)
    {
        return ArrayFunctions::array_map_recursive($value, function ($var) {
            return trim($var);
        });
    }

    /** @inheritDoc */
    public function reverseModify(array $item): array
    {
        return $item;
    }
}
