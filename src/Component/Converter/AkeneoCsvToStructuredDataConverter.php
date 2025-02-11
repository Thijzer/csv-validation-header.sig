<?php

namespace Misery\Component\Converter;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;

class AkeneoCsvToStructuredDataConverter implements ConverterInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private $csvHeaderContext;
    private $options = [
        'list' => null,
    ];

    public function __construct(AkeneoCsvHeaderContext $csvHeaderContext)
    {
        $this->csvHeaderContext = $csvHeaderContext;
    }

    public function convert(array $item): array
    {
        $codes = $this->getOption('list');
        $keyCodes = is_array($codes) ? array_keys($codes): null;
        $separator = '-';
        $output = [];

        foreach ($item as $key => $value) {

            $keys = explode($separator, $key);
            if ($keyCodes && false === in_array($keys[0], $keyCodes)) {
                continue;
            }

            if (strpos($key, '-unit') !== false) {
                unset($item[$key]);
                continue;
            }

            # values
            $prep = $this->csvHeaderContext->create($item)[$key];
            $prep['data'] = $value;

            # metric exception
            if ($codes[$keys[0]] === 'pim_catalog_metric') {
                $prep['unit'] = $item[str_replace($keys[0], $keys[0].'-unit', $key)] ?? null;
            }

            $output['values'][$keys[0]][] = $prep;
            unset($item[$key]);
        }

        return $item+$output;
    }

    public function revert(array $item): array
    {
        $output = [];
        foreach ($item['values'] as $key => $value) {
            foreach ($value as $valueItem) {
                $output[$valueItem['key']] = $valueItem['data'];
                if (isset($valueItem['unit'])) {
                    $output[$valueItem['key'].'-unit'] = $valueItem['unit'];
                }
            }
        }

        unset($item['values']);

        return $item+$output;
    }

    public function getName(): string
    {
        return 'akeneo/product/csv';
    }
}