<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class HashAction implements OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'hash';

    private $options = [
        'key' => null,
        'field' => null,
    ];

    public function apply(array $item): array
    {
        $field = $this->getOption('field', $this->getOption('key')); # legacy key
        if (array_key_exists($field, $item)) {
            $item[$field] = (string) crc32($item[$field]);
        }

        return $item;
    }
}
