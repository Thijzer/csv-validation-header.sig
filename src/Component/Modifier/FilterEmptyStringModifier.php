<?php

namespace Misery\Component\Modifier;

use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Common\Modifier\RowModifier;

class FilterEmptyStringModifier implements RowModifier
{
    public const NAME = 'filter_empty';

    public function modify($value)
    {
        return ArrayFunctions::array_filter_recursive($value, function ($var) {
            return $var !== NULL;
        });
    }

    /** @inheritDoc */
    public function reverseModify(array $item): array
    {
        return $item;
    }
}
