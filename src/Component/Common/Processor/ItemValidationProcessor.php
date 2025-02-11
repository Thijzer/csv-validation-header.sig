<?php

namespace Misery\Component\Common\Processor;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Registry\RegistryInterface;
use Misery\Component\Reader\ItemReaderAwareInterface;
use Misery\Component\Reader\ReaderInterface;
use Misery\Component\Validator\ValidatorInterface;

class ItemValidationProcessor
{
    /** @var RegistryInterface */
    private $registry;
    /** @var array */
    private $readers;

    public function setRegistry(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function getRegistry(): RegistryInterface
    {
        return $this->registry;
    }

    private function getValidationClass(string $alias)
    {
        return $this->getRegistry()->filterByAlias($alias);
    }

    private function parseContext(array $context): array
    {
        $rules = [];
        $rules['name'] = $context['name'] ?? null;

        foreach ($context['validations']['property'] ?? [] as $property => $converters) {
            foreach ($converters as $converterName => $converterOptions) {
                if ($class = $this->getValidationClass($converterName)) {
                    $rules['validations'][$property][$converterName] = [
                        'class' => $class,
                        'options' => $converterOptions,
                    ];
                }
            }
        }

        return $rules;
    }

    public function process($data, array $context = []): void
    {
        if ($data instanceof ReaderInterface) {
            $this->processReader($data, $context);
            return;
        }

        if (is_array($data)) {
            $this->processItem($data, $context);
        }
    }

    private function processReader(ReaderInterface $reader, array $context)
    {
        foreach ($reader->getIterator() as $data) {
            $this->process($data, $context);
        }
    }

    private function processItem(array $data, $context)
    {
        // preparation
        $context = $this->parseContext($context);

        foreach ($context['validations'] as $property => $namedMatches) {
            foreach ($namedMatches as $columnName => $matches) {
                $this->processMatch($data, $property, $matches, $context['name']);
            }
        }
    }

    private function processMatch(array &$data, string $property, $match, string $name = null)
    {
        /** @var ValidatorInterface $class */
        $class = $match['class'];

        if ($class instanceof OptionsInterface && !empty($match['options'])) {
            $class->setOptions($match['options']);
        }

        if ($class instanceof ItemReaderAwareInterface) {
          //  $class->setReader($this->readers['current']);
            return;
        }

        $class->validate($data[$property], array_filter([
            'property' => $property,
            'value' => $data[$property],
            'name' => $name,
        ]));
    }
}