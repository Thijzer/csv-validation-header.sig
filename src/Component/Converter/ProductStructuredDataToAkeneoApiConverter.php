<?php

namespace Misery\Component\Converter;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;

class ProductStructuredDataToAkeneoApiConverter implements ConverterInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private $csvHeaderContext;
    private $options = [
        'attributes:list' => null,
        'localizable_codes:list' => null,
    ];

    public function convert(array $item): array
    {
        return $item;
    }

    public function revert(array $item): array
    {
        $attrList = $this->getOption('attributes:list');

        foreach ($item['values'] ?? [] as $key => $valueSet) {
            $type = $attrList[$key];

            foreach ($valueSet as $i => $value) {
                unset($item['values'][$key][$i]['key']);
            }
        }

        return $item;
    }

    public function getName(): string
    {
        return 'akeneo/product/api';
    }
}