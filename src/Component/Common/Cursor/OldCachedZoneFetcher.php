<?php

namespace Misery\Component\Common\Cursor;

/** @deprecated */
class OldCachedZoneFetcher
{
    /** @var CursorInterface */
    private $cursor;

    /** @var array */
    private $ranges = [];
    /** @var ZoneFileIndexer */
    private $indexes;

    public function __construct(CursorInterface $cursor, string $reference)
    {
        $this->indexes = new OldZoneIndexer($cursor, $reference);
        $this->cursor = $cursor;
    }

    public function get(string $reference)
    {
        $zone = $this->indexes->getZoneByReference($reference);
        if (null === $zone) {
            return false;
        }

        if (!isset($this->ranges[$zone])) {
            $range = $this->indexes->getRangeFromZone($zone);

            $this->cursor->seek(current($range));
            while ($row = $this->cursor->current()) {
                $this->ranges[$zone][$this->cursor->key()] = $row;
                if (\count($this->ranges[$zone]) === \count($range)) {
                    break;
                }
                $this->cursor->next();
            }
            // only keep 3 ranges, unset the first one
            if (count($this->ranges) > 3) {
                $first = current(array_keys($this->ranges));
                unset($this->ranges[$first]);
            }
        }

        $i = $this->indexes->getFileIndexByReference($reference);

        return $this->ranges[$zone][$i] ?? false;
    }
}
