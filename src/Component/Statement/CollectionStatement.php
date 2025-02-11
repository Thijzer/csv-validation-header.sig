<?php

namespace Misery\Component\Statement;

use Misery\Component\Action\ActionInterface;

class CollectionStatement implements PredeterminedStatementInterface
{
    public const NAME = 'COLLECTION';

    /** @var StatementCollection */
    private $collection;

    use StatementTrait;

    public function __construct(StatementCollection $collection, ActionInterface $action, array $context = [])
    {
        $this->collection = $collection;
        $this->action = $action;
        $this->context = $context;
        $this->key = 1;
        $this->conditions[$this->key]['when'] = null;
    }

    public function whenField($field, $item = null): bool
    {
        return $this->isApplicable($item);
    }

    public function isApplicable(array $item): bool
    {
        return $this->collection->isApplicable($item);
    }
}