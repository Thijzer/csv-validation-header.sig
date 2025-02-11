<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Model\DataStructure\ItemInterface;

class FrameAction implements OptionsInterface, ActionItemInterface
{
    use OptionsTrait;

    public const NAME = 'frame';

    /** @var array */
    private $options = [
        'fields' => null,
        'list' => [],
    ];

    public function applyAsItem(ItemInterface $item): void
    {
        $fields = $this->getOption('fields', $this->getOption('list'));
        if (empty($fields)) {
            return;
        }

        // lets generate a multi-dimensional array
        if (isset($fields[0])) {
            $fields = array_fill_keys($fields, null);
        }
        $item->reFrame($fields);
    }

    public function apply(array $item): array
    {
        $fields = $this->getOption('fields', $this->getOption('list'));
        // array keys listed
        if (isset($fields[0])) {
            $fields = array_fill_keys($fields, null);
        }

        $item = array_replace_recursive($fields, $item);

        $tmp = [];
        foreach (array_keys($fields) as $field) {
            $tmp[$field] = $item[$field] ?? null;
        }

        return $tmp;
    }
}