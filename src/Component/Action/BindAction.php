<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Reader\ItemReaderAwareInterface;
use Misery\Component\Reader\ItemReaderAwareTrait;
use Misery\Component\Source\SourceFilter;

class BindAction implements OptionsInterface
{
    use OptionsTrait;

    private $mapper;

    public const NAME = 'bind';

    /** @var array */
    private $options = [
        'list' => [],
        'field' => null,
        'filter' => null,
    ];

    # Usage

    public function apply(array $item): array
    {
        /** @var SourceFilter $filter */
        $filter = $this->getOption('filter');

        $list = $this->getOption('list');
        if ($field =$this->getOption('field')) {
            $list = [$field];
        }

        // don't hardcode values // auto level into the array
        foreach($list as $columnKey) {
            if (array_key_exists($columnKey, $item)) {
                $item[$columnKey] = $filter->filter($item);
                if (count($item[$columnKey]) <= 1) {
                    $item[$columnKey] = current($item[$columnKey]);
                }
            }
        }

        return $item;
    }
}