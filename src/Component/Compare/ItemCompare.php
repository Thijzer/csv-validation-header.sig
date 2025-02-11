<?php

namespace Misery\Component\Compare;

use Misery\Component\Common\Cursor\CachedCursor;
use Misery\Component\Common\Cursor\CursorInterface;
use Misery\Component\Common\Functions\ArrayFunctions as Arr;
use Misery\Component\Filter\ColumnReducer;
use Misery\Component\Item\Builder\ReferenceBuilder;
use Misery\Component\Reader\ItemReader;

class ItemCompare
{
    public const ADDED = 'ADDED';
    public const REMOVED = 'REMOVED';
    public const CHANGED = 'CHANGED';
    public const BEFORE = 'BEFORE';
    public const AFTER = 'AFTER';

    /** @var CursorInterface */
    private $master;
    /** @var CursorInterface */
    private $branch;
    /** @var array */
    private $excludes;

    public function __construct(CursorInterface $master, CursorInterface $branch, array $excludes = [])
    {
        $this->master = $master;
        $this->branch = $branch;
        $this->excludes = $excludes;
    }

    public function compare(string...$references): array
    {
        $this->branch->rewind();
        $this->master->rewind();

        $headersMaster = array_keys($this->master->current());
        $headersBranch = array_keys($this->branch->current());

        $headers = [
            'out_of_alignment' => [
                self::ADDED => array_diff($headersBranch, $headersMaster),
                self::REMOVED => array_diff($headersMaster, $headersBranch),
            ],
        ];

        // only compare what we have
        $comparableHeaders = array_diff($headersMaster, $this->excludes, ...array_values($headers['out_of_alignment']));

        $masterReader = new ItemReader($this->master instanceof CachedCursor ? $this->master : CachedCursor::create($this->master));
        $branchReader = new ItemReader($this->branch instanceof CachedCursor ? $this->branch : CachedCursor::create($this->branch));

        if (\count($references) > 1) {
            $masterCodes = ReferenceBuilder::build($masterReader, ...$references);
            $reference = key($masterCodes);
            $masterCodes = current($masterCodes);
            $branchCodes = ReferenceBuilder::buildValues($branchReader, ...$references);
        } else {
            $reference = current($references);
            // compare the master with the branch
            $masterCodes = ReferenceBuilder::buildValues($masterReader, $reference);
            $branchCodes = ReferenceBuilder::buildValues($branchReader, $reference);
        }

        $changes = [
            'headers' => $headers,
            'items' => [
                self::ADDED => array_diff($branchCodes, $masterCodes),
                self::REMOVED => array_diff($masterCodes, $branchCodes),
                self::CHANGED => [],
            ],
        ];

        // pointers to the comparable items
        $pointers = array_diff($masterCodes, $changes['items'][self::REMOVED]);

        // flip codes so we can get find the NEW $lineNumber
        $codes = array_flip($branchCodes);

        foreach ($masterReader->index(array_keys($pointers))->getIterator() as $lineNumber => $master) {
            $id = $masterCodes[$lineNumber];
            $branch = current($branchReader->index([$codes[$id]])->getItems());
            $master = ColumnReducer::reduceItem($master, ...$comparableHeaders);
            $branch = ColumnReducer::reduceItem($branch, ...$comparableHeaders);

            if ($branch != $master) {
                $mb = Arr::multiCompare($master, $branch);
                $br = Arr::multiCompare($branch, $master);
                $changeFilter = [];
                foreach ($br as $column => $columnValue) {
                    $changeFilter[$column][self::BEFORE] = $columnValue;
                    $changeFilter[$column][self::AFTER] = $mb[$column] ?? null;
                }
                if ($changeFilter === []) {
                    foreach ($mb as $column => $columnValue) {
                        $changeFilter[$column][self::BEFORE] = $br[$column] ?? null;
                        $changeFilter[$column][self::AFTER] = $columnValue;
                    }
                }

                if ($changeFilter !== []) {
                    $changes['items'][self::CHANGED][$lineNumber] = [
                        'reference' => $id,
                        'line_number' => $lineNumber,
                        'changes' => $changeFilter,
                    ];
                }
            }
        }
        $changes['stats'] = [
            'headers_alignment_count' => count($changes['headers']['out_of_alignment'][self::ADDED]) + count($changes['headers']['out_of_alignment'][self::REMOVED]),
            'removed_count' => count($changes['items'][self::REMOVED]),
            'added_count' => count($changes['items'][self::ADDED]),
            'changes_count' => count($changes['items'][self::CHANGED]),
            'item_master_count' => $this->master->count(),
            'item_branch_count' => $this->branch->count(),
        ];

        return $changes;
    }
}