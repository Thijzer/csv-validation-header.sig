<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class FilterFieldAction implements OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'filter_field';

    /** @var array */
    private $options = [
        'fields' => [],
        'excludes' => [],
        'starts_with' => null,
        'ends_with' => null,
        'contains' => null,
        'reverse' => false,
        'clear_value' => false,
    ];

    public function apply($item)
    {
        $excludes = $this->getOption('excludes');
        $fields = $this->getOption('fields');
        $clearValue = $this->getOption('clear_value');
        // we want a reverse functionality when clearing values
        if ($clearValue) {
            $this->setOption('reverse', true);
        }

        if ($fields !== []) {
            foreach ($fields as $field) {
                if (false === in_array($field, $excludes)) {
                    $item[$field] = null;
                    if (false === $clearValue) {
                        unset($item[$field]);
                    }
                }
            }
            $this->setOptions(['fields' => []]); # reset fields array for next action
            return $item;
        }

        $startsWith = $this->getOption('starts_with');
        $endsWith = $this->getOption('ends_with');
        $contains = $this->getOption('contains');
        $reverse = $this->getOption('reverse');

        $fields = array_filter(array_keys($item), function ($field) use ($startsWith, $endsWith, $contains, $reverse) {
            return ($startsWith && \str_starts_with($field, $startsWith) === $reverse) ||
                ($endsWith && \str_ends_with($field, $endsWith) === $reverse) ||
                ($contains && \str_contains($field, $contains) === $reverse);
        });

        if ($fields !== []) {
            $this->setOptions(['fields' => $fields]);
            return $this->apply($item);
        }

        return $item;
    }
}