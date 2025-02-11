<?php

namespace Misery\Component\Parser;

use Assert\Assertion;
use Misery\Component\Common\Cursor\CursorInterface;
use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Filter\ColumnReducer;

class CsvParser implements CursorInterface
{
    public const DELIMITER = ';';
    public const ENCLOSURE = '"';
    public const ESCAPE = '\\';
    public const INVALID_SKIP = 'skip';
    public const INVALID_STOP = 'stop';
    public const INVALID_SKIP_ON_LARGER = 'skip_on_larger';

    /** @var array|false|mixed|string */
    private $headers;
    /** @var \SplFileObject */
    private $file;
    /** @var int|null */
    private $count;
    private $invalidLines;

    public function __construct(
        \SplFileObject $file,
        string $delimiter = self::DELIMITER,
        string $enclosure = self::ENCLOSURE,
        string $escapeChar = self::ESCAPE,
        string $invalidLines = self::INVALID_STOP
    ) {
        Assertion::file($file->getRealPath());

        $this->file = $file;
        $this->invalidLines = $invalidLines;

        $this->file->setFlags(
            \SplFileObject::READ_CSV |
            \SplFileObject::SKIP_EMPTY |
            \SplFileObject::READ_AHEAD |
            \SplFileObject::DROP_NEW_LINE
        );
        $this->file->setCsvControl($delimiter, $enclosure, $escapeChar);

        $this->seekBom();

        if (null === $this->headers) {
            $this->headers = $this->current();
            $this->next();
        }
    }

    public static function create(
        string $filename,
        string $delimiter = self::DELIMITER,
        string $enclosure = self::ENCLOSURE,
        string $escapeChar = self::ESCAPE,
        string $invalidLines = self::INVALID_STOP
    ): self {
        return new self(new \SplFileObject($filename), $delimiter, $enclosure, $escapeChar, $invalidLines);
    }

    private function seekBom(): void
    {
        $bom = $this->file->fread(3);
        if ($bom === "\xEF\xBB\xBF") {
            $this->file->fseek(3);
        } else {
            $this->file->fseek(0);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function loop(callable $callable): void
    {
        foreach ($this->getIterator() as $row) {
            $callable($row);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \Generator
    {
        while ($this->valid()) {
            yield $this->key() => $this->current();
            $this->next();
        }

        $this->rewind();
    }

    /**
     * {@inheritDoc}
     * @throws Exception\InvalidCsvElementSizeException
     * @return false|array
     */
    public function current(): mixed
    {
        $current = $this->file->current();
        if (false === $current || null === $this->headers) {
            return $current;
        }

        if (count($current) !== count($this->headers)) {
            if ($this->invalidLines === self::INVALID_SKIP_ON_LARGER && count($current) < count($this->headers)) {
                return ArrayFunctions::arrayCombine($this->headers, $current);
            }
            if ($this->invalidLines === self::INVALID_SKIP) {
                $this->next();
                return $this->current();
            }
            if ($this->invalidLines === self::INVALID_STOP) {
                $distinctHeaderIndex = ArrayFunctions::arrayDiff(array_flip($this->headers), array_keys($current));
                throw new Exception\InvalidCsvElementSizeException(
                    $this->file->getFilename(),
                    $this->key(),
                    ColumnReducer::reduceItem($this->headers, ...$distinctHeaderIndex),
                    ColumnReducer::reduceItem($current, ...$distinctHeaderIndex),
                );
            }
        }

        return array_combine($this->headers, $current);
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        $this->file->next();
    }

    /**
     * {@inheritDoc}
     */
    public function key(): mixed
    {
        return $this->file->key();
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return $this->file->valid();
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        if (false === $this->valid()) {
            $this->count = $this->key() - 1;
        }

        $this->count();
        $this->file->rewind();

        // move 1 up for the headers
        $this->next();
    }

    /**
     * {@inheritDoc}
     */
    public function seek($pointer): void
    {
        $this->file->seek($pointer);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        if (null === $this->count) {
            $this->loop(static function (){});
        }

        return $this->count;
    }

    public function clear(): void
    {
        // TODO: Implement clear() method.
    }
}