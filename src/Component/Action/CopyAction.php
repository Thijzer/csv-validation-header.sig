<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Converter\Matcher;
use Misery\Model\DataStructure\ItemInterface;

class CopyAction implements OptionsInterface, ActionItemInterface
{
    use OptionsTrait;

    public const NAME = 'copy';

    /** @var array */
    private $options = [
        'from' => null,
        'to' => null,
    ];

    public function applyAsItem(ItemInterface $item): void
    {
        $from = $this->getOption('from');
        $to = $this->getOption('to');

        if (null === $from || null === $to) {
            return;
        }

        $item->copyItem($from, $to);
    }

    public function apply(array $item): array
    {
        $to = $this->getOption('to');
        $from = $this->getOption('from');

        $matched = $this->findMatchedValueData($item, $from);
        if ($matched) {
            $matcher = $item[$matched]['matcher']->duplicateWithNewKey($to);
            $item[$matcher->getMainKey()] = $item[$matched];
            $item[$matcher->getMainKey()]['matcher'] = $matcher;

            return $item;
        }

        if (!isset($item[$from])) {
            return $item;
        }

        $item[$to] = $item[$from];

        return $item;
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