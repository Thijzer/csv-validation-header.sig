<?php

namespace Misery\Component\Converter;

use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Configurator\ConfigurationAwareInterface;
use Misery\Component\Configurator\ConfigurationTrait;

class KliumCsvToStructuredDataConverter implements ConverterInterface, RegisteredByNameInterface, OptionsInterface, ConfigurationAwareInterface
{
    use OptionsTrait;
    use ConfigurationTrait;

    private $csvHeaderContext;

    private $options = [
        'list' => null,
    ];

    public function convert(array $item): array
    {
        $output = [];
        $output['sku'] = $item['SKU'];
        $output['values'] = [];

        foreach ($item['Attribuut'] ?? [] as $i => $itemSet) {
            if ($itemSet['ID']) {
                $output['values'][$itemSet['ID']][] = [
                    'data' => $itemSet['Waarde'],
                    'locale' => null,
                    'scope' => null,
                    'key' => 'klium_'.$itemSet['ID'], # original key
                ];
            }
        }

        return $output;
    }

    public function revert(array $item): array
    {
        $output = [];
        foreach ($item['values'] as $value) {
            foreach ($value as $valueItem) {
                $output[$valueItem['key']] = $valueItem['data'];
                if (isset($valueItem['unit'])) {
                    $output[$valueItem['key'].'-unit'] = $valueItem['unit'];
                }
            }
        }

        unset($item['values']);

        $headerList = $this->getOption('list');
        if (is_string($headerList)) {
            $keys = array_keys($this->getConfiguration()->getList($headerList));
            ksort($keys);
            $this->setOption(
                'list',
                $headerList = array_fill_keys($keys, null)
            );
        }

        return $item+array_merge($headerList, $output);
    }

    public function getName(): string
    {
        return 'klium/product/csv';
    }
}