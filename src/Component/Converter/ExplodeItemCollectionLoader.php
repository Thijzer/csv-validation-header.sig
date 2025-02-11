<?php

namespace Misery\Component\Converter;

use Misery\Component\Reader\ItemCollection;

class ExplodeItemCollectionLoader implements ItemCollectionLoaderInterface
{
    private array $listItemsToLoad;
    private array $listItemsToLoop;

    public function __construct($listItemsToLoad, $listItemsToLoop)
    {
        $this->listItemsToLoad = $listItemsToLoad;
        $this->listItemsToLoop = $listItemsToLoop;
    }

    public function load(array $item): ItemCollection
    {
        return new ItemCollection($this->convert($item));
    }

    private function convert(array $item): array
    {
        $rows = [];

        if (empty($this->listItemsToLoop)) {
            // if no list items to loop are provided, we use the keys of the item and exclude the items to load
            $this->listItemsToLoop = array_diff(array_keys($item), $this->listItemsToLoad);
        }

        foreach ($this->listItemsToLoop as $itemToLoop) {
            $rowData = [];
            foreach ($this->listItemsToLoad as $itemToLoad) {
                $rowData[$itemToLoad] = $item[$itemToLoad] ?? null;
            }
            $rowData[$itemToLoop] = $item[$itemToLoop] ?? null;
            $rows[] = $rowData;
        }

        return $rows;
    }
}