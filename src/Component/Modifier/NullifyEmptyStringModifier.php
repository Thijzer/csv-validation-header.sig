<?php

namespace Misery\Component\Modifier;

use Misery\Component\Common\Modifier\RowModifier;

class NullifyEmptyStringModifier implements RowModifier
{
    public const NAME = 'nullify';

    public function modify($value)
    {
        if (\is_array($value)) {
            return array_map(function ($row) {
                return $this->modify($row);
            }, $value);
        }

        return '' === $value ? null : $value;
    }

    /** @inheritDoc */
    public function reverseModify($value)
    {
        if (\is_array($value)) {
            return array_map(function ($row) {
                return $this->reverseModify($row);
            }, $value);
        }

        return is_null($value) ? '' : $value;
    }
}