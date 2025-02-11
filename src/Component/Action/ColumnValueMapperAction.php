<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class ColumnValueMapperAction implements OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'column_value_mapper';

    /** @var array */
    private $options = [
        'list' => [],
    ];

    public function apply(array $item): array
    {
        $list = $this->getOption('list');
        foreach ($item as $column => $value) {
            if (!isset($list[$column . '-' . $value])) {
                continue;
            }

            $item[$column] = $list[$column . '-' . $value];
        }

        return $item;
    }
}