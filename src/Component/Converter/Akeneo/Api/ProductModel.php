<?php

namespace Misery\Component\Converter\Akeneo\Api;

use Misery\Component\Converter\AkeneoProductApiConverter;

class ProductModel extends AkeneoProductApiConverter
{
    private array $options = [
        'identifier' => 'sku',
        'structure' => 'matcher', # matcher OR flat
        'container' => 'values',
        'render_associations' => true,
        'render_values' => true,
        'allow_empty_string_values' => true,
    ];

    public function __construct()
    {
        $this->setOptions($this->options);
    }

    public function getName(): string
    {
        return 'akeneo/product_model/api';
    }
}