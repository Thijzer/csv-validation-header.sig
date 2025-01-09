<?php

namespace Misery\Component\Converter;

use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;

class XmlDataConverter implements ConverterInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private $csvHeaderContext;
    private $options = [
        'container' => null,
        'plurals' => [
            'categories' => 'category',
            'groups' => 'group',
        ]
    ];

    public function convert(array $item): array
    {
        return $item;
    }

    public function revert(array $item): array
    {
        $tmp = [];
        $old = [];

        foreach ($item['values'] as $code => $value) {
            foreach ($value as $i => $valueItem) {
                if (empty($valueItem['data'])) {
                    continue;
                }
                $data = $valueItem['data'];
                $old[$valueItem['key']] = $valueItem['data'];

                // we don't need this context
                unset($valueItem['data'], $valueItem['key']);

                $tmp['values'][$code] = $data;
                if (!empty($valueItem = array_filter($valueItem))) {
                    $tmp['values'][$code] = [
                        '@attributes' => $valueItem,
                        '@data' => $data,
                    ];
                    if (isset($valueItem['locale'])) {
                        $tmp['values'][$code] = [
                            '@attributes' => $valueItem,
                            '@CDATA' => $data,
                        ];
                    }
                }
            }
        }
        unset($item['values']);

        // type issues should be done by the decoder not the converter
        foreach ($item as $code => $itemValue) {
            if (is_bool($itemValue)) {
                $item[$code] = (string) $itemValue;
            }
            // single array
            if (is_array($itemValue) && isset($itemValue[0])) {
                foreach ($itemValue as $i => $itemValueValue) {
                    if (!empty($itemValueValue)) {
                        $name = isset($this->options['plurals'][$code]) ? $this->options['plurals'][$code] . '-' . $i : (string) $i;
                        $item[$code][$name] = $itemValueValue;
                    }

                    unset($item[$code][$i]);
                }
            }
        }

        $item = ArrayFunctions::array_filter_recursive($item, function ($value) {
            return !empty($value) && $value !== 0;
        });

        // id attribute
        $tmp['@attributes'] = ['id' => $old['sku']];

        return [$this->options['container'] => $item+$tmp];
    }

    public function getName(): string
    {
        return 'induxx/product/xml';
    }
}