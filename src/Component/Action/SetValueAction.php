<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Converter\Matcher;
use Misery\Model\DataStructure\ItemInterface;

class SetValueAction implements ActionItemInterface, OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'set';

    /** @var array */
    private $options = [
        'key' => null,
        'field' => null,
        'value' => null,
        'allow_creation' => false,
    ];

    public function applyAsItem(ItemInterface $item): void
    {
        $allowCreation = $this->getOption('allow_creation');
        $field = $this->getOption('field', $this->getOption('key'));
        $value = $this->getOption('value');

        if ($allowCreation) {
            $item->addItem($field, $value);
            return;
        }

        if ($item->hasItem($field)) {
            $item->editItemValue($field, $value);
        }
    }

    public function apply(array $item): array
    {
        $allowCreation = $this->getOption('allow_creation');
        $field = $this->getOption('field', $this->getOption('key'));
        $value = $this->getOption('value');

        $field = $this->findMatchedValueData($item, $field) ?? $field;

        // matcher based data object
        if (isset($item[$field]['data'])) {
            $item[$field]['data'] = $value;
            return $item;
        }

        if ($allowCreation) {
            $item[$field] = $value;
            return $item;
        }

        // don't allow array expansion when the field is not found
        // use the expand function for this
        if (key_exists($field, $item)) {
            $item[$field] = $value;
        }

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