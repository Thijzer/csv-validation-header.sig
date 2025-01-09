<?php

namespace Misery\Component\Statement;

use Misery\Component\Action\ActionInterface;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Common\Registry\RegistryInterface;

class StatementFactory implements RegisteredByNameInterface
{
    private $registryCollection;

    public function addRegistry(RegistryInterface $registry): self
    {
        $this->registryCollection[$registry->getAlias()] = $registry;

        return $this;
    }

    public function createFromConfiguration(array $configuration): StatementCollection
    {
        return new StatementCollection($this->prepRulesFromConfiguration($configuration));
    }

    private function prepRulesFromConfiguration(array $configuration): array
    {
        $rules = [];
        foreach ($configuration ?? [] as $entry) {
            $operator = $entry['when']['operator'] ?? null;
            $action = $entry['then']['action'] ?? null;
            $context = $entry['context'] ?? [];
            $statement = $this->getNamedStatement($operator);
            $action = $this->getNamedAction($action);

            if ($statement && $action) {
                /** @var StatementInterface $statement */
                $statement = $statement::prepare($action, $context);
                $statement
                    ->when($entry['when']['field'], $entry['when']['state'] ?? null)
                    ->then($entry['then']['field'], $entry['then']['state'] ?? null)
                ;
                $rules[] = $statement;
            }
        }

        return $rules;
    }

    /**
     * @param string $name
     * @return null|ActionInterface
     */
    private function getNamedAction(string $name)
    {
        return $this->registryCollection['action']->filterByAlias($name) ?? null;
    }

    /**
     * @param string $name
     * @return null|StatementInterface
     */
    private function getNamedStatement(string $name)
    {
        return $this->registryCollection['statement']->filterByAlias($name) ?? null;
    }

    public function getName(): string
    {
        return 'statement';
    }
}