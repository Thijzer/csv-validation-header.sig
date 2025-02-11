<?php

namespace Misery\Component\Action;

use Misery\Component\Akeneo\AkeneoValuePicker;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Pipeline\Exception\InvalidItemException;

class CombineAction implements ActionInterface, OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'combine';

    /** @var array */
    private $options = [
        'keys' => [],
        'locale' => null,
        'scope' => null,
        'separator' => ' ',
        'header' => null,
        'fail' => false,
        'allow_null' => false,
    ];

    public function apply(array $item): array
    {
        $sep = $this->getOption('separator');
        $values = [];
        foreach ($this->options['keys'] as $key) {
            $values[] = AkeneoValuePicker::pick($item, $key, $this->options);
        }

        $values = array_filter($values);

        $value = implode($sep, $values);

        if ($this->getOption('allow_null') && count($values) < 2) {
            $item[$this->options['header']] = null;
            return $item;
        }

        $item[$this->options['header']] = $value;

        return $item;
    }
}
