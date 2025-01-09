<?php

namespace Misery\Component\Converter;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Configurator\ConfigurationAwareInterface;
use Misery\Component\Configurator\ConfigurationTrait;

class RelatedProductsCsvToStructuredDataConverter implements ConverterInterface, OptionsInterface, RegisteredByNameInterface, ConfigurationAwareInterface
{
    use OptionsTrait;
    use ConfigurationTrait;

    private $header;
    private $options = [
        'attributes_map' => null,
        'locales' => null,
    ];

    public function convert(array $itemCollection): array
    {
        if (null === $this->header) {
            $this->header = [
                'SKU' => null,
                'BOM' => [],
                'ACCESSOIRE' => [],
                'ALTERNAT' => [],
                'CONSUMABLE' => [],
            ];
        }
        $output = $this->header;

        foreach (array_filter($itemCollection) as $item) {
            $output['SKU'] = $item['SKU'];
            $output[$item['PRODUCT_TYPE']][] = $item['LINKED_ARTICLE_SKU'];
        }

        return $output;
    }

    public function revert(array $item): array
    {
        return $item;
    }

    public function getName(): string
    {
        return 'as400/related-product/csv';
    }
}