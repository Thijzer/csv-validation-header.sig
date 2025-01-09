<?php

namespace Component\Filter;

use Misery\Component\Filter\ItemSortFilter;
use Misery\Component\Filter\ItemTreeSortFilter;
use Misery\Component\Reader\ItemCollection;
use Misery\Component\Reader\ItemReader;
use Misery\Component\Reader\ReaderInterface;
use PHPUnit\Framework\TestCase;

class ItemTreeSortFilterTest extends TestCase
{
    private array $categories = [
        [
            'id' => '5',
            'code' => 'AB',
            'parent' => '',
            'sort' => '0',
            'label' => [
                'nl_BE' => 'AB-nlBE',
                'fr_BE' => 'AB-frBE',
            ],
        ],
        [
            'id' => '7',
            'code' => 'CD',
            'parent' => 'AB',
            'sort' => '2',
            'label' => [
                'nl_BE' => 'CD-nlBE',
                'fr_BE' => 'CD-frBE',
            ],
        ],
        [
            'id' => '6',
            'code' => 'EF',
            'parent' => 'AB',
            'sort' => '1',
            'label' => [
                'nl_BE' => 'EF-nlBE',
                'fr_BE' => 'EF-frBE',
            ],
        ],
        [
            'id' => '3',
            'code' => 'GH',
            'parent' => 'EF',
            'sort' => '1',
            'label' => [
                'nl_BE' => 'GH-nlBE',
                'fr_BE' => 'GH-frBE',
            ],
        ],
        [
            'id' => '1',
            'code' => 'IJ',
            'parent' => 'AB',
            'sort' => '3',
            'label' => [
                'nl_BE' => 'IJ-nlBE',
                'fr_BE' => 'IJ-frBE',
            ],
        ],
    ];

    public function testTreeSort()
    {
        // Create a mock ItemReader
        $reader = new ItemReader(new ItemCollection($this->categories));

        $config = [
            'id_field' => 'code',
            'parent_field' => 'parent',
            'sort_children_on' => [
                'sort' => 'ASC',
            ],
        ];

        // Call the sort method
        $sortedReader = ItemTreeSortFilter::sort($reader, $config);

        // Assert that the sortedReader is an instance of ReaderInterface
        $this->assertInstanceOf(ReaderInterface::class, $sortedReader);

        $expected = ['5', '6', '7', '1', '3'];
        $this->assertEquals(
            $expected,
            array_column(iterator_to_array($sortedReader->getIterator()), 'id')
        );
    }
}
