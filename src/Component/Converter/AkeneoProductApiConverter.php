<?php

namespace Misery\Component\Converter;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;

class AkeneoProductApiConverter implements ConverterInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private $options = [
        'identifier' => 'sku',
        'structure' => 'matcher', # matcher OR flat
        'container' => 'values',
        'render_associations' => true,
        'render_values' => true,
        'allow_empty_string_values' => true,
    ];

    public function convert(array $item): array
    {
        $container = $this->getOption('container');
        if (false === isset($item[$container])) {
            return $item;
        }

        $tmp = [];
        // first we need to convert the values
        foreach ($item[$container] ?? [] as $key => $valueSet) {
            foreach ($valueSet ?? [] as  $value) {
                $matcher = Matcher::create($container.'|'.$key, $value['locale'], $value['scope']);
                $tmp[$keyMain = $matcher->getMainKey()] = $value['data'] ?? null;
                if ($this->getOption('structure') === 'matcher') {
                    $tmp[$keyMain] = $value;
                    $tmp[$keyMain]['matcher'] = $matcher;
                }
            }
        }

        unset($item[$container]);

        return $item+$tmp;
    }

    public function revert(array $item): array
    {
        $container = $this->getOption('container');
        $identifier = $this->getOption('identifier');

        $allowEmptyStringValues = $this->getOption('allow_empty_string_values');

        if (isset($item[$identifier])) {
            $item['identifier'] = $item[$identifier];
            unset($item[$identifier]);
        }
        if (array_key_exists('categories', $item)) {
            // NULL is not an excepted value
            if ($item['categories'] === [] && false === $allowEmptyStringValues) {
                unset($item['categories']);
            }elseif ($item['categories'] === null) {
                unset($item['categories']);
            }
        }

        foreach ($item ?? [] as $key => $itemValue) {
            $matcher = $itemValue['matcher'] ?? null;
            $value = $itemValue['data']['amount'] ?? $itemValue['data'] ?? null;
            if ($matcher && false === $allowEmptyStringValues && $value === '') {
                unset($item[$key]);
                continue;
            }

            if ($matcher && $matcher->matches($container)) {
                unset($itemValue['matcher']);
                unset($item[$key]);
                $item[$container][$matcher->getPrimaryKey()][] = $itemValue;
            }
        }

        if (!$this->getOption('render_associations')) {
            unset($item['associations']);
        }
        if (!$this->getOption('render_values')) {
            unset($item['values']);
        }

        return $item;
    }

    public function getName(): string
    {
        return 'akeneo/product/api';
    }
}