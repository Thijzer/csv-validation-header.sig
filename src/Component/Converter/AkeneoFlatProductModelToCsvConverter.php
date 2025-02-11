<?php

namespace Misery\Component\Converter;

use Misery\Component\Akeneo\Header\AkeneoHeaderTypes;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Reader\ReaderAwareInterface;
use Misery\Component\Reader\ReaderAwareTrait;

/**
 * This Converter converts flat product to std data
 * We focus on correcting with minimal issues
 * The better the input you give the better the output
 */
class AkeneoFlatProductModelToCsvConverter extends AkeneoFlatProductToCsvConverter
{
    private $options = [
        'properties' => ['code', 'family', 'family_variant'],
    ];

    public function convert(array $item): array
    {
        $this->setOptions($this->options);

        return parent::convert($item);
    }

    public function getName(): string
    {
        return 'flat/akeneo/product_model/csv';
    }
}