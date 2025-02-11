<?php

namespace Misery\Component\Converter;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Common\Registry\RegistryInterface;
use Misery\Component\Configurator\Configuration;
use Misery\Component\Configurator\ConfigurationAwareInterface;

class ConverterFactory implements RegisteredByNameInterface
{
    private $registryCollection;

    public function addRegistry(RegistryInterface $registry): ConverterFactory
    {
        $this->registryCollection[$registry->getAlias()] = $registry;

        return $this;
    }

    public function getConverterFromRegistry(string $converterName): ConverterInterface
    {
        return $this->registryCollection['converter']->filterByAlias($converterName);
    }

    public function createMultipleConfigurations(array $configuration, Configuration $config): void
    {
        foreach ($configuration as $configurationItem) {
            $this->createFromConfiguration($configurationItem, $config);
        }
    }

    public function createFromConfiguration(array $configuration, Configuration $config): ConverterInterface
    {
        if (false === isset($configuration['name'])) {
            throw new \Exception('Converter must have a name');
        }

        /** @var ConverterInterface $converter */
        $converter = clone $this->registryCollection['converter']->filterByAlias($configuration['name']);
        if (null === $converter) {
            throw new \Exception(sprintf('Converter named %s not found', $configuration['name']));
        }

        if ($converter instanceof OptionsInterface && isset($configuration['options'])) {
            $options = $configuration['options'];
            $converter->setOptions($options);
        }

        if ($converter instanceof ConfigurationAwareInterface) {
            $converter->setConfiguration($config);
        }

        if ($converter instanceof OptionsInterface && isset($configuration['options'])) {
            $options = $configuration['options'];
            // fetch those values
            foreach ($options as $option => $optionValue) {
                if (strpos($option, ':list') !== false) {
                    $converter->setOptions([$option => $config->getList($optionValue)]);
                }
            }

            if ($list = $converter->getOption('list')) {
                $converter->setOptions(['list' => $config->getList($list)]);
            }
        }

        return $converter;
    }

    public function getName(): string
    {
        return 'converter';
    }
}