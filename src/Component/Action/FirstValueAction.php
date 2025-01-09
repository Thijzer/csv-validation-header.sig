<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Converter\Matcher;
use Misery\Model\DataStructure\ItemInterface;

class FirstValueAction implements ActionInterface
{
    use OptionsTrait;

    private $mapper = null;

    public const NAME = 'first_value';

    /** @var array */
    private $options = [
        'fields' => [],
        'store_field' => null,
        'default_value' => null,
    ];

    public function applyAsItem(ItemInterface $item): void
    {
        $defaultValue = $this->getOption('default_value');
        $storeField = $this->getOption('store_field');
        $fields = $this->getOption('fields');

        foreach ($fields as $field) {
            if (!empty($item->getItem($field)->getDataValue())) {
                $item->copyItem($field, $storeField);
                return;
            }
        }

        $item->addItem($storeField, $defaultValue);
    }

    public function apply(array $item): array
    {
        $defaultValue = $this->getOption('default_value');
        $storeField = $this->getOption('store_field');
        $fields = $this->getOption('fields');
        $matcher = Matcher::create('values|'.$storeField);

        foreach ($fields as $field) {
            $field = $this->findMatchedValueData($item, $field) ?? $field;

            // COPY matcher if match is found
            if (isset($item[$field]['matcher'])) {
                $matcher = $item[$field]['matcher']->duplicateWithNewKey($storeField);
                $item[$matcher->getMainKey()] = $item[$field];
                $item[$matcher->getMainKey()]['matcher'] = $matcher;
                return $item;
            }
        }

        if (!isset($item[$matcher->getMainKey()])) {
            $item[$matcher->getMainKey()] = [
                'matcher' => $matcher,
                'data' => $defaultValue,
                'locale' => null,
                'scope' => null,
            ];
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