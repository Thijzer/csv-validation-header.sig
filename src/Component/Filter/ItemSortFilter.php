<?php

namespace Misery\Component\Filter;

use Misery\Component\Item\Builder\ReferenceBuilder;
use Misery\Component\Reader\ItemReader;
use Misery\Component\Reader\ItemReaderInterface;
use Misery\Component\Reader\ReaderInterface;

class ItemSortFilter
{
    /**
     * PLEASE don't use the sort on very large data sets
     * array_multisort can only sort on the whole data_set in memory
     */
    public static function sort(ItemReaderInterface $reader, array $criteria, array $context = []): ItemReaderInterface
    {
        $flags = ['ASC' => SORT_ASC, 'DSC' => SORT_DESC, 'DESC' => SORT_DESC];
        $sortTypes = ['string' => SORT_STRING, 'numeric' => SORT_NUMERIC];
        $setup = [];
        foreach ($criteria as $keyName => $sortDirection) {
            $setup[] = $index = ReferenceBuilder::buildValues($reader, $keyName);
            $setup[] = $flags[strtoupper($sortDirection)];
            $setup[] = $sortTypes[$context['sort_type'] ?? 'numeric'];
            $setup[] = array_keys($index);
        }

        array_multisort(...$setup);

        return $reader->index(end($setup));
    }

    public static function preSort(ItemReader $reader, array $criteria): ReaderInterface
    {
        $sorted = $reader->getItems();
        usort($sorted, function (ReferenceBuilder $a, ReferenceBuilder $b) use ($criteria) {
            foreach ($criteria as $criterion) {
                $aValue = $a->get($criterion['property']);
                $bValue = $b->get($criterion['property']);

                if ($aValue === $bValue) {
                    continue;
                }

                if ($criterion['direction'] === 'asc') {
                    return $aValue < $bValue ? -1 : 1;
                }

                return $aValue > $bValue ? -1 : 1;
            }

            return 0;
        });

        return new ItemReader($sorted);
    }
}