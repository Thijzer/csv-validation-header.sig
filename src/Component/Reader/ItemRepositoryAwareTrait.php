<?php

namespace Misery\Component\Reader;

use Misery\Component\Common\Repository\ItemRepository;

trait ItemRepositoryAwareTrait
{
    private $repository;

    public function setRepository(ItemRepository $repository): void
    {
        $this->repository = $repository;
    }

    public function getRepository(): ItemRepository
    {
        return $this->repository;
    }
}