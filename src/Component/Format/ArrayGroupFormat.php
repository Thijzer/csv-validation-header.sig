<?php

namespace Misery\Component\Format;

use Misery\Component\Common\Format\ArrayFormat;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class ArrayGroupFormat implements ArrayFormat, OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'group';

    private array $options = [
        'field' => null,
        'separator'  => '-',
    ];

    /** @inheritDoc */
    public function format(array $item): array
    {
        return $this->explodeKeys(
            $item,
            $this->getOption('field'),
            $this->getOption('separator')
        );
    }


    public function reverseFormat($value): array
    {
        return $this->implodeKeys($value, $this->getOption('separator'));
    }


    private function explodeKeys(array $item, string $field, string $separator = '-'): array
    {
        foreach ($item as $key => &$value) {
            if (str_starts_with($key, $field) && str_contains($key, $separator)) {
                $explodedKeys = explode($separator, $key);
                $lastKey = array_pop($explodedKeys);

                $currentArray = &$item;
                foreach ($explodedKeys as $subKey) {
                    if (!isset($currentArray[$subKey])) {
                        $currentArray[$subKey] = [];
                    }
                    $currentArray = &$currentArray[$subKey];
                }

                $currentArray[$lastKey] = $value;
                unset($item[$key]);
            }
        }

        return $item;
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