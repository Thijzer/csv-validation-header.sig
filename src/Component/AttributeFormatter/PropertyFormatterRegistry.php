<?php

namespace Misery\Component\AttributeFormatter;

use Misery\Component\Reader\ItemCollection;
use Misery\Component\Reader\ItemReader;

class PropertyFormatterRegistry
{
    /** @var ItemCollection */
    private ItemCollection $formatters;
    private ItemReader $items;

    public function __construct()
    {
        $this->formatters = new ItemCollection();
        $this->items = new ItemReader($this->formatters);
    }

    public function add(PropertyFormatterInterface $formatter): void
    {
        $this->formatters->set(get_class($formatter), $formatter);
    }

    public function addAll(PropertyFormatterInterface...$formatters): void
    {
        foreach ($formatters as $formatter) {
            $this->add($formatter);
        }
    }

    public function findByType($type): \Generator
    {
        return $this->items->filter(static function (PropertyFormatterInterface $formatter) use ($type) {
            return $formatter->supports($type);
        })->getIterator();
    }
}