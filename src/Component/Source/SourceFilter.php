<?php

namespace Misery\Component\Source;

use Misery\Component\Reader\ItemReaderInterface;
use Misery\Component\Source\Command\ExecuteSourceCommandInterface;
use Misery\Component\Statement\ItemPlaceholder;

class SourceFilter
{
    private $command;

    public function __construct(ExecuteSourceCommandInterface $command)
    {
        $this->command = $command;
    }

    /**
     * @param array $item
     * @return ItemReaderInterface|array
     */
    public function filter(array $item)
    {
        $options = $this->createOptionsFromItem($item);

        // prep the options
        return $this->command->executeWithOptions($options);
    }

    private function createOptionsFromItem(array $item): array
    {
        $options = $this->command->getOptions();

        foreach ($options['filter'] as $key => $value) {
            $field = ItemPlaceholder::extract($value);
            if ($field !== $value && isset($item[$field])) {
                $options['criteria'][$key] = $item[$field];
                unset($options['filter']);
                continue;
            }
            // @deprecated
            $field = ltrim($value, '$');
            if (str_starts_with($value, '$') !== false && isset($item[$field])) {
                $options['criteria'][$key] = $item[$field];
                unset($options['filter']);
            }
        }

        return $options;
    }
}