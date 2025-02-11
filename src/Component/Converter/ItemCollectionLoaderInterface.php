<?php

namespace Misery\Component\Converter;

use Misery\Component\Reader\ItemCollection;

interface ItemCollectionLoaderInterface
{
    public function load(array $item): ItemCollection;
}