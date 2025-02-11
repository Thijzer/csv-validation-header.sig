<?php

namespace Misery\Component\Modifier;

use Misery\Component\Common\Modifier\CellModifier;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class DecodeSpecialModifier implements CellModifier, OptionsInterface
{
    use OptionsTrait;
    const NAME = 'decode_special';

    private $options = [
        'decoder' => 'quotes',
    ];

    private $flags = [
        'double quotes' => ENT_COMPAT,
        'quotes' => ENT_QUOTES,
        'no quotes' => ENT_NOQUOTES,
        'html 4.01' => ENT_HTML401,
        'xml 1' => ENT_XML1,
        'xhtml' => ENT_XHTML,
        'html 5' => ENT_HTML5,
    ];

    public function modify(string $value)
    {
        return html_entity_decode($value, $this->flags[$this->options['decoder']] ?? null);
    }
}