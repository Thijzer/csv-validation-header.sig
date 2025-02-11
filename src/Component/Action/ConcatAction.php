<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Utils\ValueFormatter;
use Misery\Model\DataStructure\ItemInterface;

class ConcatAction implements OptionsInterface, ActionItemInterface
{
    use OptionsTrait;

    public const NAME = 'concat';

    /** @var array */
    private $options = [
        'key' => null,
        'field' => null,
        'format' => '%s',
    ];

    public function applyAsItem(ItemInterface $item): void
    {
        $format = $this->getOption('format');
        $field = $this->getOption('field', $this->getOption('key'));
        if (null == $field) {
            return;
        }

        $dataValues = [];
        foreach (ValueFormatter::getKeys($format) as $key) {
            $dataValues[$key] = $item->getItem($key)?->getDataValue();
        }

        $item->addItem(
            $field,
            ValueFormatter::format($format, $dataValues)
        );
    }

    public function apply(array $item): array
    {
        $field = $this->getOption('field', $this->getOption('key'));
        if (null == $field) {
            return $item;
        }

        // don't check if array_key_exist here, concat should always work, if the field doesn't exist
        // somewhat the point to form a new field from concatenation
        $item[$field] = ValueFormatter::format($this->getOption('format'), $item);

        return $item;
    }
}