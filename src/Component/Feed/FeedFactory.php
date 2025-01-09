<?php

namespace Misery\Component\Feed;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Common\Registry\RegistryInterface;
use Misery\Component\Configurator\Configuration;
use Misery\Component\Configurator\ConfigurationAwareInterface;
use Misery\Component\Feed\FeedInterface;

class FeedFactory implements RegisteredByNameInterface
{
    private array $registryCollection;

    public function addRegistry(RegistryInterface $registry): self
    {
        $this->registryCollection[$registry->getAlias()] = $registry;

        return $this;
    }

    public function createFromConfiguration(array $configuration, Configuration $config): FeedInterface
    {
        /** @var FeedInterface $converter */
        $feed = $this->registryCollection['feed']->filterByAlias($configuration['name']);

        if ($feed instanceof OptionsInterface && isset($configuration['options'])) {
            $options = $configuration['options'];
            $feed->setOptions($options);
        }

        if ($feed instanceof ConfigurationAwareInterface) {
            $feed->setConfiguration($config);
        }

        if ($feed instanceof OptionsInterface && isset($configuration['options'])) {
            $options = $configuration['options'];
            // fetch those values
            foreach ($options as $option => $optionValue) {
                if (strpos($option, ':list') !== false) {
                    $feed->setOptions([$option => $config->getList($optionValue)]);
                }
            }

            if ($list = $feed->getOption('list')) {
                $feed->setOptions(['list' => $config->getList($list)]);
            }
        }

        return $feed;
    }

    public function getName(): string
    {
        return 'feed';
    }
}