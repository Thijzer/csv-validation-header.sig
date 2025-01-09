<?php

namespace Misery\Component\Parser;

use Assert\Assert;
use Misery\Component\Common\Cursor\CursorInterface;

class XmlParser implements CursorInterface
{
    public const CONTAINER = 'Container';

    /** @var string */
    private $container;
    private $xml;
    /** @var int|null */
    private $count;
    private $i = 0;

    public function __construct(
        string $file,
        string $container = null
    ) {
        $this->container = $container;

        $this->xml = new \XMLReader();
        $this->xml->open($file);
    }

    public static function create(string $filename, string $container = null): self
    {
        return new self($filename, $container);
    }

    public function setContainer(string $container): void
    {
        // once's set, you can't chang the container
        $this->container = $this->container ?? $container;
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
     *
     * @return false|array
     *
     * @throws \Exception
     */
    public function current(): mixed
    {
        // this part is responsible for setting the start element correctly
        while ($this->i === 0 && $this->xml->read() && $this->xml->name !== $this->container) {
            // digging;
            Assert::that($this->container, 'XML parser needs a container name')->notEmpty();
        }
        $this->i++;

        try {
            if ($this->xml->name === $this->container) {
                return json_decode(json_encode(new \SimpleXMLElement($this->xml->readOuterXML())), true);
            }
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        $this->xml->next($this->container);
    }

    /**
     * {@inheritDoc}
     */
    public function key(): mixed
    {
        return $this->i;
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return false !== $this->current();
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
        $this->seek(0);

        // move 1 up for the headers
        $this->next();
    }

    /**
     * {@inheritDoc}
     */
    public function seek($offset): void
    {
        $this->xml->moveToAttributeNo($offset);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        if (null === $this->count) {
            $this->loop(function (){});
        }

        return $this->count;
    }

    public function clear(): void
    {
        // TODO: Implement clear() method.
    }
}