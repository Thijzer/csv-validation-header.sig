<?php

namespace Misery\Component\Common\Cursor;

/** @deprecated */
class OldZoneIndexer
{
    private const MEDIUM_CACHE_SIZE = 5000;

    /** @var array */
    private $indexes;
    private $zones;
    private $ranges;

    public function __construct(CursorInterface $cursor, string $reference)
    {
        // prep indexes
        $cursor->loop(function ($row) use ($cursor, $reference) {
            $zone = (int) (($cursor->key() -1) / self::MEDIUM_CACHE_SIZE);
            $reference = $row[$reference];
            $this->zones[$reference] = $zone;
            $this->indexes[$reference] = $cursor->key();
            $this->ranges[$zone][] = $cursor->key();
        });
        $cursor->rewind();
    }

    public function getFileIndexByReference(string $reference)
    {
        return $this->indexes[$reference] ?? null;
    }

    public function getZoneByReference($reference)
    {
        return $this->zones[$reference] ?? null;
    }

    public function getRangeFromZone(int $zone): array
    {
        return $this->ranges[$zone] ?? [];
    }
}