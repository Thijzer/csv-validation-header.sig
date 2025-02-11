<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Model\DataStructure\ItemInterface;

class RemoveAction implements OptionsInterface, ActionItemInterface
{
    use OptionsTrait;

    public const NAME = 'remove';

    /** @var array */
    private $options = [
        'keys' => [],
        'fields' => [],
    ];

    public function applyAsItem(ItemInterface $item): void
    {
        $fields = $this->getOption('keys', $this->getOption('fields'));
        foreach ($fields as $field) {
            $item->removeItem($field);
        }
    }

    public function apply(array $item): array
    {
        $fields = $this->getOption('keys', $this->getOption('fields'));
        foreach ($fields as $field) {
            unset($item[$field]);
        }

        return $item;
    }
}