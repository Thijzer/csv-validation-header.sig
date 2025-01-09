<?php

namespace Misery\Component\Source;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Common\Registry\RegistryInterface;
use Misery\Component\Source\Command\ExecuteSourceCommandInterface;

class ListFactory implements RegisteredByNameInterface
{
    private $registryCollection;

    public function addRegistry(RegistryInterface $registry): self
    {
        $this->registryCollection[$registry->getAlias()] = $registry;

        return $this;
    }

    public function createFromConfiguration(array $configuration, SourceCollection $collection): array
    {
        $commands = [];
        foreach ($configuration as $listItem) {
            if (isset($listItem['values']) && is_array($listItem['values'])) {
                $commands[$listItem['name']] = $listItem['values'];
                continue;
            }
            if (isset($listItem['source_command']) && $class = $this->getCommandClass($listItem['source_command'])) {

                if ($class instanceof OptionsInterface && isset($listItem['options'])) {
                    $options = $listItem['options'];
                    $class->setOptions($options);
                }

                if ($class instanceof SourceAwareInterface && isset($listItem['source'])) {
                    $class->setSource($collection->get($listItem['source']));
                }

                // this might become a late call
                $commands[$listItem['name']] = $class->execute();
            }
        }

        return $commands;
    }

    private function getCommandClass(string $alias): ?ExecuteSourceCommandInterface
    {
        $filter = $this->registryCollection['source_command']->filterByAlias($alias);

        return clone $filter;
    }

    public function getName(): string
    {
        return 'list';
    }
}