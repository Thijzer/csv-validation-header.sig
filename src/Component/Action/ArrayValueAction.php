<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class ArrayValueAction implements OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'array_value';

    private $options = [
        'field' => null,
        'column_item' => null,
        'function' => null,
    ];

    public function apply(array $item): array
    {
        $field = $this->getOption('field');
        $columnItem = $this->getOption('column_item');

        switch ($this->options['function']) {
            case 'array_push':
                $item = $this->arrayPush($item, $field, $columnItem);
                break;
            case 'array_merge':
                $item = $this->arrayMerge($item, $field, $columnItem);
                break;
            default:
                throw new \Exception('Function not found');
        }

        return $item;
    }

    private function arrayPush(array $item, string $field, string $columnItem): array
    {
        if (array_key_exists($columnItem, $item) && array_key_exists($field, $item) && is_array($item[$field])) {
            $item[$field][] = $item[$columnItem];
        }

        return $item;
    }

    private function arrayMerge(array $item, $field, $columnItem): array
    {
        if (array_key_exists($columnItem, $item) && array_key_exists($field, $item) && is_array($item[$field]) && is_array($item[$columnItem])) {
            $item[$field] = array_merge($item[$field], $item[$columnItem]);
        }

        return $item;
    }
}
