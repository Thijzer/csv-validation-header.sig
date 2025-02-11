<?php

namespace Misery\Component\Action;

use Misery\Model\DataStructure\ItemInterface;

interface ActionItemInterface extends ActionInterface
{
    public function applyAsItem(ItemInterface $item): void;
}