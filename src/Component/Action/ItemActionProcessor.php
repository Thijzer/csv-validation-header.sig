<?php

namespace Misery\Component\Action;

use Misery\Model\DataStructure\ItemInterface;

class ItemActionProcessor
{
    public function __construct(private readonly array $configurationRules) {}

    public function process(ItemInterface|array $item): ItemInterface|array
    {
        foreach ($this->configurationRules as $name => $action) {
            if ($item instanceof ItemInterface) {
                $action->applyAsItem($item);
                continue;
            }
            $item = $action->apply($item);
        }

        return $item;
    }
}