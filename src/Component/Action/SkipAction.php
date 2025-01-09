<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Pipeline\Exception\SkipPipeLineException;
use Misery\Model\DataStructure\ItemInterface;

class SkipAction implements OptionsInterface, ActionItemInterface
{
    use OptionsTrait;

    public const NAME = 'skip';

    /** @var array */
    private $options = [
        'field' => null,
        'state' => ['EMPTY'],
        'skip_message' => ''
    ];
    private array $values = [];

    public function applyAsItem(ItemInterface $item): void
    {
        $field = $this->getOption('field');
        $dateValue = $item->getItem($field)?->getDataValue();

        $this->apply([$field => $dateValue]);
    }

    public function apply(array $item): array
    {
        $field = $this->getOption('field');
        $state = $this->getOption('state');
        $states = is_string($state) ? [$state]: $state;

        $message = $this->getOption('skip_message', '');
        $forceSkip = $this->getOption('force_skip', false);

        if ($forceSkip) {
            throw new SkipPipeLineException($message);
        }

        if (is_array($field) && isset($field['code']) && isset($field['index'])) {
            $value = (isset($item[$field['code']][$field['index']])) ? $item[$field['code']][$field['index']] : null;
        } else {
            $value = $item[$field] ?? null;
        }

        foreach ($states as $state) {
            if ($state === 'EMPTY' && empty($value)) {
                throw new SkipPipeLineException($message);
            }

            if ($state === 'UNIQUE') {
                if (isset($this->values[$value])) {
                    throw new SkipPipeLineException($message);
                }
                $this->values[$value] = '';
                continue;
            }

            if ($value === $state) {
                throw new SkipPipeLineException($message);
            }
        }

        return $item;
    }

    public function __destruct()
    {
        $this->values = [];
    }
}