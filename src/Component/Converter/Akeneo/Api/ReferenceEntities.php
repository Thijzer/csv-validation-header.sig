<?php
declare(strict_types=1);

namespace Misery\Component\Converter\Akeneo\Api;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Converter\AkeneoCsvHeaderContext;
use Misery\Component\Converter\ConverterInterface;

class ReferenceEntities implements ConverterInterface, RegisteredByNameInterface, OptionsInterface
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
        $this->csvHeaderContext->unsetHeader();
        $separator = '-';
        $output = [];

        foreach ($item as $key => $value) {
            if (in_array($key, ['code', 'labels', '%reference-code%'])) {
                continue;
            }

            $keys = explode($separator, $key);
            # values
            $prep = $this->csvHeaderContext->create($item)[$key];

            $prep['channel'] = $prep['scope'];
            unset($prep['scope']);

            $prep['data'] = $value;
            unset($prep['key']);

            $output['values'][$keys[0]][] = $prep;
            unset($item[$key]);
        }

        return $item+$output;
    }

    public function revert(array $item): array
    {
       return $item;
    }

    public function getName(): string
    {
        return 'akeneo/reference_entities/api';
    }
}
