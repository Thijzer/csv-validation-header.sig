<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

/**
 * @deprecated
 * A pop action on 3 elements should pop the last element not pop all elements but the last element
 */
class PopAction implements OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'pop';

    /** @var array */
    private $options = [
        'field' => null,
        'separator' => null,
    ];

    public function apply(array $item): array
    {
        $field = $this->getOption('field');
        if (false === array_key_exists($field, $item)) {
            return $item;
        }

        $value = explode($this->options['separator'], $item[$field]);
        $item[$field] = array_pop($value);

        return $item;
    }
}