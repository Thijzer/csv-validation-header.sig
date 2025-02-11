<?php

namespace Misery\Component\Converter\Akeneo\Csv;

use Misery\Component\Converter\AkeneoCsvHeaderContext;

class ProductModel extends Product
{
    private array $options = [
        'container' => 'values',
        'default_currency' => 'EUR',
        'single_currency' => true,
        'attribute_types:list' => null, # this key value list is optional, improves type matching for options, metrics, prices
        'identifier' => 'code',
        'properties' => [
            'code' => [
                'text' => null,
            ],
            'enabled' => [
                'boolean' => null,
            ],
            'family' => [
                'text' => null,
            ],
            'categories'=> [
                'list' => null,
            ],
            'parent' => [
                'text' => null,
            ],
        ],
        'parse' => [],
    ];

    public function __construct(AkeneoCsvHeaderContext $csvHeaderContext)
    {
        $this->setOptions($this->options);
        parent::__construct($csvHeaderContext);
    }

    public function getName(): string
    {
        return 'akeneo/product_model/csv';
    }
}
