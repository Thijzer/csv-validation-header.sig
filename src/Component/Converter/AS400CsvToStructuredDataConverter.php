<?php

namespace Misery\Component\Converter;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Configurator\ConfigurationAwareInterface;
use Misery\Component\Configurator\ConfigurationTrait;

class AS400CsvToStructuredDataConverter implements ConverterInterface, OptionsInterface, RegisteredByNameInterface, ConfigurationAwareInterface
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
            $this->header = (new AS400HeaderContext())->create(array_values($this->getOption('attributes_map')), $this->getOption('locales'));
        }
        $output = $this->header;
        $mapping = $this->getOption('attributes_map');

        foreach ($itemCollection as $item) {
            $output['sku'] = $item['SKU'];
            if ($item['LOCALE'] === '') {
                // some cases the locale is empty
                // see article 01010411
                continue;
            }
            // no need to process unmapped attributes
            if (!in_array($item['TYPE'], array_keys($mapping))) {
                continue;
            }

            $output[implode('-', [$mapping[$item['TYPE']], $item['LOCALE']])] = $item['DESCRIPTION'];
        }

        return $output;
    }

    public function revert(array $item): array
    {
        return $item;
    }

    public function getName(): string
    {
        return 'as400/product/csv';
    }
}