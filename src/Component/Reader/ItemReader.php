<?php

namespace Misery\Component\Reader;

use Misery\Component\Common\Cursor\CursorInterface;
use Misery\Component\Common\Cursor\ItemCursor;

class ItemReader implements ItemReaderInterface
{
    private $cursor;

    public function __construct(\Iterator $cursor)
    {
        $this->cursor = $cursor;
    }

    /** @inheritDoc */
    public function read()
    {
        $item = $this->cursor->current();
        if ($item === false) {
            return false;
        }

        $this->cursor->next();

        return $item;
    }

    public function index(array $lines): ItemReaderInterface
    {
        return new self($this->processIndex($lines));
    }

    private function processIndex(array $lines): \Generator
    {
        foreach ($lines as $lineNr) {
            $this->seek($lineNr);
            yield $lineNr => $this->cursor->current();
        }
    }

    /**
     * Adds seek support for \Iterator objects
     */
    public function seek($pointer): void
    {
        $this->cursor->rewind();
        while ($this->cursor->valid()) {
            if ($this->cursor->key() === $pointer) {
                break;
            }
            $this->cursor->next();
        }

        // @TODO throw outofboundexception
    }

    public function filterByList(array $constraint): ReaderInterface
    {
        $reader = $this;
        $columnName = $constraint['field'];
        $list = $constraint['list'];

        $reader = $reader->filter(static function ($row) use ($columnName, $list) {
            return in_array($row[$columnName], $list);
        });

        return $reader;
    }

    public function find(array $constraints): ReaderInterface
    {
        $reader = $this;
        foreach ($constraints as $columnName => $rowValue) {
            if (is_array($rowValue)) {
                $reader = $reader->filter(static function ($row) use ($rowValue, $columnName) {
                    return in_array($row[$columnName], $rowValue);
                });
            } elseif (is_string($rowValue) && in_array($rowValue, ['UNIQUE', 'IS_NOT_NUMERIC', 'NOT_EMPTY', 'NOT_NULL'])) {
                if ($rowValue === 'UNIQUE') {
                    $list = [];
                    $reader = $reader->filter(static function ($row) use ($columnName, &$list) {
                        $id = $row[$columnName];
                        if (in_array($id, $list, true)) {
                            return false;
                        }
                        $list[] = $id;
                        return true;
                    });
                } elseif ($rowValue === 'IS_NOT_NUMERIC') {
                    $reader = $reader->filter(static function ($row) use ($columnName) {
                        return !is_numeric($row[$columnName]);
                    });
                } elseif ($rowValue === 'NOT_EMPTY') {
                    $reader = $reader->filter(static function ($row) use ($columnName) {
                        return !empty($row[$columnName]);
                    });
                } elseif ($rowValue === 'NOT_NULL') {
                    $reader = $reader->filter(static function ($row) use ($columnName) {
                        return false === ($row[$columnName] === NULL);
                    });
                }
            } else {
                $reader = $reader->filter(static function ($row) use ($rowValue, $columnName) {
                    return $row[$columnName] === $rowValue;
                });
            }
        }

        return $reader;
    }

    public function filter(callable $callable): ReaderInterface
    {
        return new self($this->processFilter($callable));
    }

    private function processFilter(callable $callable): \Generator
    {
        foreach ($this->getIterator() as $key => $row) {
            if (true === $callable($row)) {
                yield $key => $row;
            }
        }
    }

    public function map(callable $callable): ReaderInterface
    {
        return new self($this->processMap($callable));
    }

    private function processMap(callable $callable): \Generator
    {
        foreach ($this->getIterator() as $key => $row) {
            if (is_array($row)) {
                yield $key => $callable($row);
            }
        }
    }

    public function getCursor(): CursorInterface
    {
        return new ItemCursor($this);
    }

    public function getIterator(): \Iterator
    {
        return $this->cursor;
    }

    public function clear(): void
    {
        if ($this->cursor instanceof CursorInterface) {
            $this->cursor->clear();
        }
    }

    public function getItems(): array
    {
        return iterator_to_array($this->cursor);
    }
}
