<?php

namespace Misery\Component\Source;

interface SourceCollectionAwareInterface
{
    public function getSource(string $alias):? Source;
    public function setSourceCollection(SourceCollection $collection): void;
    public function getSourceCollection(): SourceCollection;
}