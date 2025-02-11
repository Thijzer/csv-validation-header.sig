<?php

namespace Misery\Component\Common\Modifier;

interface RowModifier extends Modifier
{
    public function modify(array $value);
    public function reverseModify(array $value);
}