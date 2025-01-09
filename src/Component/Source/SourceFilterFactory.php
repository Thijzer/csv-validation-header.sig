<?php

namespace Misery\Component\Source;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Common\Registry\RegistryInterface;
use Misery\Component\Source\Command\ExecuteSourceCommandInterface;

class SourceFilterFactory implements RegisteredByNameInterface
{
    private $registryCollection;

    public function addRegistry(RegistryInterface $registry): SourceFilterFactory
    {
        $this->registryCollection[$registry->getAlias()] = $registry;

        return $this;
    }

    public function createFromConfiguration(array $configuration, SourceCollection $sourceCollection): array
    {
        // we have to apply the filterCollection like an encoder, so we can execute on demand
        $filterCollection = [];
        foreach ($configuration as $listCommand) {
            $filterCollection[$listCommand['name']] = new SourceFilter(
                $class = $this->getCommandClass()
            );

            if ($class instanceof OptionsInterface && isset($listCommand['options'])) {
                $options = $listCommand['options'];
                $class->setOptions($options);
            }

            if ($class instanceof SourceAwareInterface && isset($listCommand['source'])) {
                // @todo allow cache options
                $class->setSource($sourceCollection->get($listCommand['source']));
            }
        }

        return $filterCollection;
    }

    private function getCommandClass(): ExecuteSourceCommandInterface
    {
        $filter = $this->registryCollection['source_command']->filterByAlias('filter');

        return clone $filter;
    }

    public function getName(): string
    {
        return 'filter';
    }
}