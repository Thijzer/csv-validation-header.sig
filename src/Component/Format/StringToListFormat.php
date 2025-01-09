<?php

namespace Misery\Component\Format;

use Misery\Component\Common\Format\StringFormat;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class StringToListFormat implements StringFormat, OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'list';

    /** @var array */
    private $options = [
        'array_target' => [],
        'separator' => ',',
    ];

    public function format(string $value): array
    {
        if (empty($value)) {
            return [];
        }

//        if (\is_array($value) && !empty($this->options['array_target'])) {
//            $value = ArrayFunctions::flatten($value);
//            foreach ($this->options['array_target'] as $target) {
//                if (isset($value[$target])) {
//                    $value[$target] = $this->format($value[$target]);
//                }
//            }
//
//            return ArrayFunctions::unflatten($value);
//        }
//
        return explode($this->options['separator'], $value);
    }

    /**
     * @param array $value
     */
    public function reverseFormat($value)
    {
        if (empty($value)) {
            return '';
        }

        $val = [];
        foreach ($value as $key => $item) {
            if  (empty($item)) {
                $val[$key] = null;
                continue;
            }

            if (is_string($key)) {
                $val[$key] = $this->reverseFormat($item);
            } elseif (is_int($key)) {
                return implode($this->options['separator'], $value);
            }
        }

        return $val;
    }
}
