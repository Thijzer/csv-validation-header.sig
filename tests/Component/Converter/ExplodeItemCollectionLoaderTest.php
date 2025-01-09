<?php

namespace Tests\Misery\Component\Converter;

use Misery\Component\Converter\ExplodeItemCollectionLoader;
use Misery\Component\Reader\ItemCollection;
use PHPUnit\Framework\TestCase;

class ExplodeItemCollectionLoaderTest extends TestCase
{
    public function testLoad()
    {
        // Sample input data
        $listItemsToLoad = ['item1', 'item2'];
        $listItemsToLoop = ['item3', 'item4'];
        $item = ['item1' => 'value1', 'item2' => 'value2', 'item3' => 'value3', 'item4' => 'value4'];

        // Construct the loader
        $loader = new ExplodeItemCollectionLoader($listItemsToLoad, $listItemsToLoop);

        // Load the item
        $loadedCollection = $loader->load($item);

        // Expected output
        $expectedRows = [
            ['item1' => 'value1', 'item2' => 'value2', 'item3' => 'value3'],
            ['item1' => 'value1', 'item2' => 'value2', 'item4' => 'value4'],
        ];

        // Assert the output
        $this->assertInstanceOf(ItemCollection::class, $loadedCollection);
        $this->assertEquals($expectedRows, $loadedCollection->getItems());

        // Test with empty $listItemsToLoop
        $loaderWithEmptyListItemsToLoop = new ExplodeItemCollectionLoader([], $listItemsToLoad);
        $loadedCollectionWithEmptyListItemsToLoop = $loaderWithEmptyListItemsToLoop->load($item);

        // Expected output when $listItemsToLoop is empty
        $expectedRowsWithEmptyListItemsToLoop = [
            ['item1' => 'value1'], ['item2' => 'value2']
        ];

        // Assert the output when $listItemsToLoop is empty
        $this->assertInstanceOf(ItemCollection::class, $loadedCollectionWithEmptyListItemsToLoop);
        $this->assertEquals($expectedRowsWithEmptyListItemsToLoop, $loadedCollectionWithEmptyListItemsToLoop->getItems());
    }
}
