<?php

namespace Misery\Component\Source\Command;

use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Filter\ColumnReducer;
use Misery\Component\Source\SourceAwareInterface;
use Misery\Component\Source\SourceTrait;
use Misery\Component\Statement\StatementBuilder;
use Misery\Component\Statement\StatementCollection;

class SourceFilterCommand implements ExecuteSourceCommandInterface, SourceAwareInterface, RegisteredByNameInterface
{
    use SourceTrait;
    use OptionsTrait;

    private $reader;

    private $options = [
        'return_value' => null,
        'return_values' => [],
        'mode' => null,
        'cache' => [],
        'filter' => null,
        'criteria' => null,
        'statement' => null,
    ];

    public function execute()
    {
        if ($this->reader === null) {
            $this->reader = $this->getSource()->getCachedReader($this->getOption('cache'));
        }

        if ($this->getOption('statement')) {
            $collection = new StatementCollection(
                StatementBuilder::fromArray(
                    $this->getOption('statement')
                )
            );

            $this->reader = $this->reader->filter(function ($item) use ($collection) {
                return $collection->isApplicable($item);
            });
        }

        $items = $this->reader;
        if ($this->getOption('criteria')) {
            $items = $this->reader->find($this->getOption('criteria'));
        }

        if (!empty($this->getOption('return_value'))) {
            $items = array_map(function (array $item) {
                $item = $this->source->encode($item);
                return $item[$this->getOption('return_value')] ?? null;
            }, $items->getItems());

            if ($this->getOption('mode') === 'merge_return_values') {
                $items = array_unique(array_merge_recursive(...$items));
            }

            return $items;
        }

        if (count($this->getOption('return_values')) > 0) {
            $items = ColumnReducer::reduce($items, ...$this->getOption('return_values'));
        }

        if ($this->getOption('mode') === 'return_first_item') {
            return $items->getIterator()->current();
        }

        return $items->getItems();
    }

    public function executeWithOptions(array $options)
    {
        $staleOptions = $this->getOptions();

        $this->setOptions($options);

        $result = $this->execute();

        $this->setOptions($staleOptions);

        return $result;
    }

    public function getName(): string
    {
        return 'filter';
    }
}