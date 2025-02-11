<?php
declare(strict_types=1);

namespace Misery\Component\Converter\Akeneo\Csv;

use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Converter\ConverterInterface;

class AttributeOption implements ConverterInterface, RegisteredByNameInterface
{
    public function convert(array $item): array
    {
        $item = ArrayFunctions::unflatten($item, '-');
        $item['labels'] = $item['label'];
        unset($item['label']);
        return $item;
    }

    public function revert(array $item): array
    {
        $item['label'] = $item['labels'];
        unset($item['labels']);
        $item = ArrayFunctions::flatten($item, '-');

        return $item;
    }

    public function getName(): string
    {
        return 'akeneo/attribute_options/csv';
    }
}
