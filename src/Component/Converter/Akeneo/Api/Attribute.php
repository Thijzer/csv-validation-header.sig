<?php
declare(strict_types=1);

namespace Misery\Component\Converter\Akeneo\Api;

use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Converter\AkeneoCsvHeaderContext;
use Misery\Component\Converter\ConverterInterface;

class Attribute implements ConverterInterface, RegisteredByNameInterface
{
    private $csvHeaderContext;
    private $boolValuesToCheck = [
        'useable_as_grid_filter',
        'wysiwyg_enabled',
        'decimals_allowed',
        'negative_allowed',
        'default_value',
        'unique',
        'localizable',
        'scopable'
    ];

    public function __construct(AkeneoCsvHeaderContext $csvHeaderContext)
    {
        $this->csvHeaderContext = $csvHeaderContext;
    }

    public function convert(array $item): array
    {
        $item = ArrayFunctions::unflatten($item, '-');
        foreach ($this->boolValuesToCheck as $boolValueToCheck) {
            if (isset($item[$boolValueToCheck]) && !is_bool($item[$boolValueToCheck])) {
                $item[$boolValueToCheck] = (bool)$item[$boolValueToCheck];
            }
        }

        return $item;
    }

    public function revert(array $item): array
    {
        return $item;
    }

    public function getName(): string
    {
        return 'akeneo/attributes/api';
    }
}
