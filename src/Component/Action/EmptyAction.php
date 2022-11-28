<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Configurator\ConfigurationAwareInterface;
use Misery\Component\Configurator\ConfigurationTrait;

class EmptyAction implements OptionsInterface, ConfigurationAwareInterface
{
    use OptionsTrait;
    use ConfigurationTrait;

    public const NAME = 'empty';

    /** @var array */
    private $options = [
        'field' => null,
        'list' => null,
    ];

    public function apply(array $item): array
    {
        $field = $this->getOption('field');
        $list = $this->getOption('list');
        $prefix = $this->getOption('prefix');

        // validation
        if ($field && !is_array($item[$field])) {
            return $item;
        }

        if (!empty($list)) {
            foreach ($list as $key) {
                if (isset($item[$field][$key]) && is_array($item[$field][$key])) {
                    foreach ($item[$field][$key] as $index => $value) {
                        if (is_array($value) && isset($value['data'])) {
                            $item[$field][$key][$index]['data'] = null;
                        }
                    }
                }
            }
        }

        if (!empty($prefix) && isset($item[$field]) && is_array($item[$field])) {
            foreach ($item[$field] as $key => $values) {
                if (is_array($values) && substr($key, 0, strlen($prefix)) === $prefix && ($list && !in_array($key, $list))) {
                    foreach ($values as $index => $value) {
                        if (isset($value['data'])) {
                            $item[$field][$key][$index]['data'] = null;
                        }
                    }
                }
            }
        }

        return $item;
    }
}