<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class RepositionKeysAction implements OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'reseat';

    /** @var array */
    private $options = [
        'from' => [],
        'to' => null,
    ];

    public function apply(array $item): array
    {
        $fields = $this->getOption('from', $this->getOption('list'));

        $tmp = [];
        if ($fields === 'all') {
            $tmp[$this->options['to']] = $item;

            return $tmp;
        }

        $fields = array_intersect($fields, array_keys($item));
        if (empty($fields)) {
            return $item;
        }

        foreach ($fields as $key) {
            $item[$this->options['to']][$key] = $item[$key];
            unset($item[$key]);
        }

        return $item;
    }
}