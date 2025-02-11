<?php

namespace Misery\Component\Common\Cursor;

class ContinuousBufferFetcher
{
    private string $indexReference;
    private array $buffer = [];
    private CursorInterface $cursor;
    private ZoneFileIndexer $indexer;
    private bool $allowFileIndexRemoval;

    public function __construct(CursorInterface $cursor, string $indexReference, bool $allowFileIndexRemoval = false)
    {
        $this->indexer = new ZoneFileIndexer();
        $this->cursor = $cursor;
        $this->indexReference = $indexReference;
        $this->allowFileIndexRemoval = $allowFileIndexRemoval;
    }

    public function get(string $reference)
    {
        $this->indexer->init($this->cursor, $this->indexReference);

        $fileIndex = $this->indexer->getFileIndexByReference($reference);
        if (null === $fileIndex) {
            return false;
        }

        $zone = $this->indexer->getZoneByFileIndex($fileIndex);
        if (false === $this->itemInBuffer($fileIndex, $zone)) {
            // new item to load in
            $this->loadBufferFromZone($zone);

            // only keep 3 ranges, unset the first one
            if (count($this->buffer) > 3) {
                $first = current(array_keys($this->buffer));
                unset($this->buffer[$first]);
            }
        }

        // clear memory
        $item = $this->buffer[$zone][$fileIndex] ?? false;
        if ($this->allowFileIndexRemoval) {
            unset($this->buffer[$zone][$fileIndex]);
            $this->indexer->removeFileIndex($reference, $fileIndex);
        }

        return $item;
    }

    private function itemInBuffer(int $index, int $zone): bool
    {
        return isset($this->buffer[$zone][$index]);
    }

    private function loadBufferFromZone(int $zone): void
    {
        $range = $this->indexer->getRangeFromZone($zone);

        $this->cursor->seek(current($range)); # reset line number
        while ($row = $this->cursor->current()) {
            $this->buffer[$zone][$this->cursor->key()] = $row;
            if (\count($this->buffer[$zone]) === \count($range)) {
                break;
            }
            $this->cursor->next();
        }
    }
}