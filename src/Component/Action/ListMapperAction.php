<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Model\DataStructure\ItemInterface;

class ListMapperAction implements OptionsInterface, ActionItemInterface
{
    use OptionsTrait;

    public const NAME = 'value_mapping_in_list';

    /** @var array */
    private $options = [
        'field' => null,
        'store_field' => null,
        'list' => [],
    ];

    public function applyAsItem(ItemInterface $item): void
    {
        if ([] === $this->getOption('list')) {
            return;
        }

        if (null === $this->getOption('field')) {
            return;
        }
        $field = $this->getOption('field');
        $storeField = $this->getOption('store_field');

        $itemNode = $item->getItem($field);
        $dataValue = $itemNode?->getDataValue();
        if ($dataValue && array_key_exists($dataValue, $this->getOption('list'))) {
            $newValue = $this->options['list'][$dataValue];
            if ($storeField) {
                $item->copyItem($storeField, $newValue);
            } else {
                $item->editItemValue($field, $newValue);
            }
        }
    }

    public function apply(array $item): array
    {
        $value = $item[$this->options['field']] ?? null;
        if ($value && array_key_exists($value, $this->options['list'])) {
            $item[$this->getOption('store_field', $this->getOption('field'))] = $this->options['list'][$value];
        }

        return $item;
    }
}