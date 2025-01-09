<?php

namespace Misery\Component\Common\Registry;

use Misery\Component\Common\Collection\ArrayCollection;

class Registry implements RegistryInterface
{
    private $collection;
    private $alias;

    public function __construct(string $alias)
    {
        $this->collection = new ArrayCollection();
        $this->alias = $alias;
    }

    public function registerNamedObject($object): self
    {
        $this->collection->set($object::NAME, $object);

        return $this;
    }

    public function register(string $alias, $object): self
    {
        $this->collection->set($alias, $object);

        return $this;
    }

    public function registerByName(RegisteredByNameInterface $object): self
    {
        $this->collection->set($object->getName(), $object);

        return $this;
    }

    public function registerAllByName(...$objects): void
    {
        foreach ($objects as $object) {
            $this->registerByName($object);
        }
    }

    public function registerAll(...$objects): void
    {
        foreach ($objects as $object) {
            $this->register($object::NAME, $object);
        }
    }

    public function filterByAlias(string $alias)
    {
        return $this->collection->get($alias)->first();
    }

    public function getAlias(): string
    {
        return $this->alias;
    }
}
