<?php

namespace Misery\Component\Modifier;

use Misery\Component\Common\Modifier\CellModifier;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class ReplaceCharacterModifier implements CellModifier, OptionsInterface
{
    use OptionsTrait;
    const NAME = 'replace_char';

    private $options = [
        'characters' => [],
    ];

    public function modify(string $value)
    {
        return strtr($value, $this->options['characters']);
    }
}