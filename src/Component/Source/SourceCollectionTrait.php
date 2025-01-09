<?php

namespace Misery\Component\Source;

trait SourceCollectionTrait
{
    /** @var SourceCollection */
    private $sourceCollection;

    public function getSource(string $alias): ? Source
    {
        return $this->sourceCollection->get($alias);
    }

    public function getSourceCollection(): SourceCollection
    {
        return $this->sourceCollection;
    }

    public function setSourceCollection(SourceCollection $collection): void
    {
        $this->sourceCollection = $collection;
    }
}