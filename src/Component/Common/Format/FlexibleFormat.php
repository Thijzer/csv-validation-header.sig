<?php

namespace Misery\Component\Common\Format;

interface FlexibleFormat extends Format
{
    /** @param array|string $value */
    public function format($value);

    /** @param string|array $value */
    public function reverseFormat($value);
}