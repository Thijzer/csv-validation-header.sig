<?php

namespace Misery\Component\Format;

use Misery\Component\Common\Format\ArrayFormat;
use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class ArrayUnflattenFormat implements ArrayFormat, OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'unflatten';

    /** @var array */
    private $options = [
        'separator' => '.',
    ];

    /** @inheritDoc */
    public function format(array $item): array
    {
        return ArrayFunctions::unflatten($item, $this->options['separator']);
    }

    /** @inheritDoc */
    public function reverseFormat($value): array
    {
        return ArrayFunctions::flatten($value, $this->options['separator']);
    }
}
