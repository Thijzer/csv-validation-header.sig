<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Converter\Matcher;
use Misery\Component\Mapping\ColumnMapper;
use Misery\Model\DataStructure\ItemInterface;

class KeyMapperAction implements OptionsInterface, ActionItemInterface
{
    use OptionsTrait;

    private ColumnMapper $mapper;

    public const NAME = 'key_mapping';

    public function __construct()
    {
        $this->mapper = new ColumnMapper();
    }

    /** @var array */
    private $options = [
        'key' => null,
        'list' => [],
        'reverse' => false,
    ];

    public function applyAsItem(ItemInterface $item): void
    {
        $reverse = $this->getOption('reverse');
        $list = array_filter($this->getOption('list'));

        if ($reverse) {
            $keys = [];
            foreach ($list as $match => $replacer) {
                if ($item->hasItem($replacer)) {
                    $item->copyItem($replacer, $match);
                    $keys[] = $replacer;
                }
            }
            foreach ($keys as $keyToUnset) {
                $item->removeItem($keyToUnset);
            }
            return;
        }

        foreach ($list as $match => $replacer) {
            if ($item->hasItem($match)) {
                $item->moveItem($match, $replacer);
            }
        }
    }

    public function apply(array $item): array
    {
        $reverse = $this->getOption('reverse');
        if ($reverse) {
            $list = array_filter($this->getOption('list'));

            $keys = [];
            foreach ($list as $key => $value) {
                $newKey = $this->findMatchedValueData($item, $key) ?? $key;
                if (array_key_exists($value, $item)) {
                    $item[$newKey] = $item[$value];
                    $keys[] = $value;
                }
            }
            // Unset the collected keys
            foreach ($keys as $keyToUnset) {
                unset($item[$keyToUnset]);
            }
            return $item;
        }

        if ($key = $this->getOption('key')) {
            $item[$key] = $this->map($item[$key], $this->getOption('list'));
            return $item;
        }

        $list = array_filter($this->getOption('list'));
        // when dealing with converted data we need the primary keys
        // we just need to replace these keys
        $newList = [];
        foreach ($list as $key => $value) {
            $newKey = $this->findMatchedValueData($item, $key) ?? $key;
            $newList[$newKey] = $value;
        }

        if (count($newList) > 0) {
            return $this->map($item, $newList);
        }

        return $this->map($item, $list);
    }

    private function map(array $item, array $list)
    {
        try {
            return $this->mapper->map($item, $list);
        } catch (\InvalidArgumentException) {
            return $item;
        }
    }

    private function findMatchedValueData(array $item, string $field): int|string|null
    {
        foreach ($item as $key => $itemValue) {
            $matcher = $itemValue['matcher'] ?? null;
            /** @var $matcher Matcher */
            if ($matcher && $matcher->matches($field)) {
                return $key;
            }
        }

        return null;
    }
}