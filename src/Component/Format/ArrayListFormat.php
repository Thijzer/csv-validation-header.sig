<?php

namespace Misery\Component\Format;

use Misery\Component\Common\Format\FlexibleFormat;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class ArrayListFormat implements FlexibleFormat, OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'multi-list';

    /** @var array */
    private $options = [
        'separator' => ',',
    ];

    /** @inheritDoc */
    public function format($item): array
    {
        $value = $this->explodeKeys($item, '-');
    }

    /** @inheritDoc */
    public function reverseFormat($item): array
    {
        $item = $this->implodeKeys($item, '-');

        foreach ($item as &$valueItem) {
            $valueItem = implode($this->getOption('separator'), $valueItem);
        }

        return $item;
    }

    private function explodeKeys(array $array, string $delimiter = '_')
    {
        $result = array();
        foreach ($array as $key => $value) {
            $path = explode($delimiter, $key);
            $ref = &$result;
            foreach ($path as $step) {
                if (!isset($ref[$step])) {
                    $ref[$step] = array();
                }
                $ref = &$ref[$step];
            }
            $ref = $value;
        }

        return $result;
    }

    private function implodeKeys(array $array, string $separator = '_', array &$result = [], $path = ''): array
    {
        foreach ($array as $key => $value) {
            $newPath = $path . $key;
            if (is_array($value) && $value !== []) {
                $this->implodeKeys($value, $separator, $result, $newPath . $separator);
            } else {
                $result[$newPath] = $value;
            }
        }

        return $result;
    }
}