<?php

namespace Misery\Component\Modifier;

use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Common\Modifier\RowModifier;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class ArrayUnflattenModifier implements RowModifier, OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'unflatten';

    private $options = [
        'separator' => '.',
    ];

    public function modify(array $item): array
    {
        return ArrayFunctions::unflatten($item, $this->options['separator']);
    }

    /** @inheritDoc */
    public function reverseModify(array $item): array
    {
        return ArrayFunctions::flatten($item, $this->options['separator']);
    }
}
