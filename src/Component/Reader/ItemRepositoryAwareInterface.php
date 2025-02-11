<?php

namespace Misery\Component\Reader;

use Misery\Component\Common\Repository\ItemRepository;

interface ItemRepositoryAwareInterface
{
    public function setRepository(ItemRepository $repository): void;

    public function getRepository(): ItemRepository;
}