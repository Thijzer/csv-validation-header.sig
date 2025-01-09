<?php

namespace Misery\Component\Modifier;

use Misery\Component\Common\Modifier\CellModifier;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class IconvEncodingModifier implements CellModifier, OptionsInterface
{
    use OptionsTrait;
    const NAME = 'iconv';

    private $options = [
        'in_charset' => 'utf-8',
        'out_charset' => null,
        'locale' => 'en_US.utf8',
    ];

    public function modify(string $value)
    {
        return iconv($this->options['in_charset'], $this->options['out_charset'], $value);
    }

    // todo iconv is an optional module within php
    // we need to validate a inside the factory to see if the module is supported or note
    // this supports methods might help the validation step
    public function supports(string $value = null): bool
    {
        setlocale(LC_ALL, $this->options['locale']);

        return extension_loaded('iconv');
    }
}