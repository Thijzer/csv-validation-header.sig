<?php

namespace Misery\Component\Source;

use Misery\Component\Common\Cursor\CachedCursor;
use Misery\Component\Common\Cursor\CursorInterface;
use Misery\Component\Item\Processor\NullProcessor;
use Misery\Component\Item\Processor\ProcessorInterface;
use Misery\Component\Reader\ItemReader;
use Misery\Component\Reader\ItemReaderInterface;

class Source
{
    private $cursor;
    private $alias;
    private $processorIn;
    private $processorOut;
    private $cache;

    public function __construct(
        CursorInterface $cursor,
        ProcessorInterface $processorIn,
        ProcessorInterface $processorOut,
        string $alias
    ) {
        $this->alias = $alias;
        $this->cursor = $cursor;
        $this->processorIn = $processorIn;
        $this->processorOut = $processorOut;
    }

    public static function createSimple(CursorInterface $cursor, string $alias): self
    {
        return new self($cursor, new NullProcessor(), new NullProcessor(), $alias);
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function encode(array $item): array
    {
        return $this->processorIn->process($item);
    }

    public function decode(array $item): array
    {
        return $this->processorOut->process($item);
    }

    public function getCursor(): CursorInterface
    {
        return $this->cursor;
    }

    public function getReader(): ItemReaderInterface
    {
        return new ItemReader(clone $this->cursor);
    }

    public function getCachedReader(array $options = []): ItemReaderInterface
    {
        if (null === $this->cache) {
            $options = array_merge(['cache_size' => CachedCursor::LARGE_CACHE_SIZE], $options);

            $this->cache = new ItemReader(new CachedCursor(
                clone $this->cursor,
                $options
            ));
        }

        return $this->cache;
    }
}