<?php

namespace Tests\Misery\Component\Common\Repository;

use Misery\Component\Common\Repository\ItemRepository;
use Misery\Component\Reader\ItemCollection;
use Misery\Component\Reader\ItemReader;
use PHPUnit\Framework\TestCase;

class ItemRepositoryTest extends TestCase
{
    private $items = [
        [
            'id' => '1',
            'first_name' => 'Gordie',
        ],
        [
            'id' => "2",
            'first_name' => 'Frans',
        ],
    ];

    public function test_find_from_file_repository(): void
    {
        $reader = new ItemReader($items = new ItemCollection($this->items));

        $repository = new ItemRepository($reader, 'id');

        $data = $repository->find('1');

        $this->assertSame($items->getItems()[0], $data);
    }

    public function test_find_with_multiple_references_from_item_repository(): void
    {
        $reader = new ItemReader($items = new ItemCollection($this->items));

        $repository = new ItemRepository($reader, 'id', 'first_name');

        $data = $repository->find('1', 'Gordie');

        $this->assertSame($items->getItems()[0], $data);
    }

    public function test_find_one_by_from_item_repository(): void
    {
        $reader = new ItemReader($items = new ItemCollection($this->items));

        $repository = new ItemRepository($reader, 'id');

        $data = $repository->findOneBy(['first_name' => 'Frans']);

        $this->assertSame($items->getItems()[1], $data);
    }

    public function test_find_by_from_item_repository(): void
    {
        $reader = new ItemReader($items = new ItemCollection($this->items));

        $repository = new ItemRepository($reader, 'id');

        $data = $repository->findBy(['first_name' => 'Frans']);

        $this->assertSame([1 => $items->getItems()[1]], $data);
    }
}