<?php
declare(strict_types=1);

namespace Misery\Component\Converter\Akeneo\Api;

use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Converter\ConverterInterface;

class FamilyVariant implements ConverterInterface, RegisteredByNameInterface
{
    public function convert(array $item): array
    {
        return $item;
    }

    public function revert(array $item): array
    {
        $item['%family%'] = $item['family'];
        unset($item['family']);
       // $item['labels'] = [];
       // $item['variant_attribute_sets'] = [];

        return $item;
    }

    public function getName(): string
    {
        return 'akeneo/family_variant/api';
    }
}
