<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Common\Registry\RegistryInterface;
use Misery\Component\Configurator\Configuration;
use Misery\Component\Configurator\ConfigurationAwareInterface;
use Misery\Component\Reader\ItemReaderAwareInterface;
use Misery\Component\Source\SourceCollection;

class ItemActionProcessorFactory implements RegisteredByNameInterface
{
    public function __construct(private readonly RegistryInterface $registry) {}

    public function createActionProcessor(SourceCollection $sources, array $configuration): ItemActionProcessor
    {
        return new ItemActionProcessor(
            $this->prepRulesFromConfiguration($sources, $configuration)
        );
    }

    public function createFromConfiguration(array $configuration, Configuration $config, ?SourceCollection $sources): ItemActionProcessor
    {
        return new ItemActionProcessor(
            $this->prepRulesFromConfiguration($sources, $configuration, $config)
        );
    }

    private function prepRulesFromConfiguration(?SourceCollection $sources, array $configuration, Configuration $config = null): array
    {
        $rules = [];
        foreach ($configuration as $name => $value) {
            $action = $value['action'] ?? null;
            unset($value['action']);

            if ($action = $this->registry->filterByAlias($action)) {

                $action = clone $action;

                if ($config && isset($value['filter']) && is_string($value['filter'])) {
                    $value['filter'] = $config->getFilter($value['filter']);
                }

                if ($action instanceof OptionsInterface && !empty($value)) {
                    if ($config && isset($value['list']) && is_string($value['list'])) {
                        $value['list'] = $config->getList($value['list']);
                    }

                    if (isset($value['filter_list'])) {
                        $value['filter_list'] = $config->getList($value['filter_list']);
                    }

                    $action->setOptions($value);
                }

                if ($action instanceof ConfigurationAwareInterface && $config instanceof Configuration) {
                    $action->setConfiguration($config);
                }

                if ($action instanceof ItemReaderAwareInterface && isset($value['source'])) {
                    $action->setReader($sources->get($value['source'])->getReader());
                }

                $rules[$name] = $action;
            }
        }

        return $rules;
    }

    public function getName(): string
    {
        return 'action';
    }
}