<?php

namespace Tests\Component\Filter;

use Misery\Component\Filter\ItemSortFilter;
use Misery\Component\Reader\ItemCollection;
use Misery\Component\Reader\ItemReader;
use Misery\Component\Reader\ReaderInterface;
use PHPUnit\Framework\TestCase;

class ItemSortFilterTest extends TestCase
{
    private array $categories = [
        [
            'id' => '5',
            'code' => 'AB',
            'label' => [
                'nl_BE' => 'AB-nlBE',
                'fr_BE' => 'AB-frBE',
            ],
        ],
        [
            'id' => '7',
            'code' => 'CD',
            'label' => [
                'nl_BE' => 'CD-nlBE',
                'fr_BE' => 'CD-frBE',
            ],
        ],
        [
            'id' => '6',
            'code' => 'EF',
            'label' => [
                'nl_BE' => 'EF-nlBE',
                'fr_BE' => 'EF-frBE',
            ],
        ],
        [
            'id' => '3',
            'code' => 'GH',
            'label' => [
                'nl_BE' => 'GH-nlBE',
                'fr_BE' => 'GH-frBE',
            ],
        ],
        [
            'id' => '1',
            'code' => 'IJ',
            'label' => [
                'nl_BE' => 'IJ-nlBE',
                'fr_BE' => 'IJ-frBE',
            ],
        ],
    ];

    public function testSortNumeric()
    {

        // Create a mock ItemReader
        $reader = new ItemReader(new ItemCollection($this->categories));

        // Define the criteria for sorting
        $criteria = [
            'id' => 'ASC',
        ];

        // Call the sort method
        $sortedReader = ItemSortFilter::sort($reader, $criteria);

        // Assert that the sortedReader is an instance of ReaderInterface
        $this->assertInstanceOf(ReaderInterface::class, $sortedReader);

        $expected = ['1', '3', '5', '6', '7'];
        $this->assertEquals(
            $expected,
            array_column(iterator_to_array($sortedReader->getIterator()), 'id')
        );
    }

    public function testSortNumericDESC()
    {

        // Create a mock ItemReader
        $reader = new ItemReader(new ItemCollection($this->categories));

        // Define the criteria for sorting
        $criteria = [
            'id' => 'DSC',
        ];

        // Call the sort method
        $sortedReader = ItemSortFilter::sort($reader, $criteria);

        // Assert that the sortedReader is an instance of ReaderInterface
        $this->assertInstanceOf(ReaderInterface::class, $sortedReader);

        $expected = ['7', '6', '5', '3', '1'];
        $this->assertEquals(
            $expected,
            array_column(iterator_to_array($sortedReader->getIterator()), 'id')
        );
    }

    public function testSortOnStringValues()
    {
        $categories = $this->categories;
        $categories[3]['code'] = 'AA'; // move

        // Create a mock ItemReader
        $reader = new ItemReader(new ItemCollection($categories));

        // Define the criteria for sorting
        $criteria = [
            'code' => 'ASC',
        ];
        $sortOptions = ['sort_type' => 'string'];

        // Call the sort method
        $sortedReader = ItemSortFilter::sort($reader, $criteria, $sortOptions);

        // Assert that the sortedReader is an instance of ReaderInterface
        $this->assertInstanceOf(ReaderInterface::class, $sortedReader);

        $expected = ['3', '5', '7', '6', '1'];
        $this->assertEquals(
            $expected,
            array_column(iterator_to_array($sortedReader->getIterator()), 'id')
        );
    }
}
