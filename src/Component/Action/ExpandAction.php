<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Model\DataStructure\ItemInterface;

class ExpandAction implements OptionsInterface, ActionItemInterface
{
    use OptionsTrait;

    public const NAME = 'expand';

    /** @var array */
    private $options = [
        'set' => null,
        'list' => null,
    ];

    public function applyAsItem(ItemInterface $item): void
    {
        $list = $this->getOption('set', $this->getOption('list', []));
        if (empty($list)) {
            return;
        }

        foreach ($list as $itemCode => $itemValue) {
            $item->addItem($itemCode, $itemValue);
        }
    }

    public function apply(array $item): array
    {
        return array_replace_recursive($this->getOption('set', $this->getOption('list', [])), $item);
    }
}