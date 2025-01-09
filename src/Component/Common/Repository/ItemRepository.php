<?php

namespace Misery\Component\Common\Repository;

use Misery\Component\Reader\ItemReaderInterface;

/**
 * A doctrine compatible Item Repository
 */
class ItemRepository
{
    private $reader;
    private $references;

    public function __construct(ItemReaderInterface $reader, string ...$references)
    {
        $this->reader = $reader;
        $this->references = $references;
    }

    public function getReferences()
    {
        return $this->references;
    }

    public function find(string ...$ids): array
    {
        $criteria = [];
        foreach ($this->references as $key => $reference) {
            $criteria[$reference] = $ids[$key] ?? null;
        }

        return $this->findOneBy($criteria);
    }

    public function findBy(array $criteria): array
    {
        return $this->reader->find($criteria)->getItems();
    }

    public function findOneBy(array $criteria): array
    {
        return current($this->findBy($criteria)) ?: [];
    }
}