<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Configurator\ConfigurationAwareInterface;
use Misery\Component\Configurator\ConfigurationTrait;

class MergeAction implements OptionsInterface, ConfigurationAwareInterface
{
    use OptionsTrait;
    use ConfigurationTrait;

    public const NAME = 'merge';

    /** @var array */
    private $options = [
        'field' => null,
        'list' => null,
    ];

    public function apply(array $item): array
    {
        $field = $this->getOption('field');
        $list = $this->getOption('list');

        // validation
        if (!is_array($item[$field])) {
            return $item;
        }

        if (is_string($list)) {
            $item[$field] = $this->configuration->getList($list);
        }
        if (is_array($list)) {
            $item[$field] = array_filter(array_merge($item[$field], $list));
        }

        return $item;
    }
}