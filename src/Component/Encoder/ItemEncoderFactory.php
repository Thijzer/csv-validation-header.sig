<?php

namespace Misery\Component\Encoder;

use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Common\Registry\RegistryInterface;
use Misery\Component\Configurator\ConfigurationManager;
use Misery\Component\Converter\ConverterInterface;

class ItemEncoderFactory implements RegisteredByNameInterface
{
    private $registryCollection;

    public function addRegistry(RegistryInterface $registry): ItemEncoderFactory
    {
        $this->registryCollection[$registry->getAlias()] = $registry;

        return $this;
    }

    public function createFromConfiguration(array $configuration, ConfigurationManager $configurationManager): ItemEncoder
    {
        // encoder can have a blueprint named reference
        if (isset($configuration['blueprint'])) {
            $bluePrint = $configurationManager->createBlueprint($configuration['blueprint']);
            if ($bluePrint) {
                return $bluePrint->getEncoder();
            }
        }

        return $this->createItemEncoder($configuration);
    }

    public function createItemEncoder(array $configuration): ItemEncoder
    {
        return new ItemEncoder($this->parseDirectivesFromConfiguration($configuration));
    }

    public function parseDirectivesFromConfiguration(array $configuration): array
    {
        $rules = [];
        foreach ($configuration['encode'] ?? [] as $property => $converters) {
            foreach ($converters as $formatName => $formatOptions) {
                if ($class = $this->getFormatClass($formatName)) {
                    $rules['property'][$property][$formatName] = [
                        'class' => $class,
                        'options' => $formatOptions,
                    ];
                }
            }
        }

        foreach ($configuration['parse'] ?? [] as $modifierName => $modifierOptions) {
            if ($class = $this->getModifierClass($modifierName)) {
                $rules['item'][$modifierName] = [
                    'class' => $class,
                    'options' => $modifierOptions,
                ];
            }
        }

        return $rules;
    }

    private function getModifierClass(string $formatName)
    {
        return $this->registryCollection['modifier']->filterByAlias($formatName);
    }

    private function getFormatClass(string $formatName)
    {
        return $this->registryCollection['format']->filterByAlias($formatName);
    }

    public function getName(): string
    {
        return 'encoder';
    }
}