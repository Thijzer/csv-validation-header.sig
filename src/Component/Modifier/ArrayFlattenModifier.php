<?php

namespace Misery\Component\Modifier;

use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Common\Modifier\RowModifier;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class ArrayFlattenModifier implements RowModifier, OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'flatten';

    private $options = [
        'separator' => '.',
        'whitelist' => [],
        'preserve_int_keys' => false,
    ];

    public function modify(array $item): array
    {
        return ArrayFunctions::flatten($item, $this->options['separator'], '', $this->getOption('whitelist'), $this->getOption('preserve_int_keys'));
    }

    /** @inheritDoc */
    public function reverseModify(array $item): array
    {
        return ArrayFunctions::unflatten($item, $this->options['separator']);
    }
}
