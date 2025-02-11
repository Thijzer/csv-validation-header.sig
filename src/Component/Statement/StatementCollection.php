<?php

namespace Misery\Component\Statement;

use Misery\Component\Common\Collection\ArrayCollection;

class StatementCollection extends ArrayCollection implements StatementInterface
{
    public function apply(array $item): array
    {
        /** @var StatementInterface $statement */
        foreach ($this->getValues() as $statement) {
            $item = $statement->apply($item);
        }

        return $item;
    }

    public function isApplicable(array $item): bool
    {
        /** @var StatementInterface $statement */
        foreach ($this->getValues() as $statement) {
            if (false === $statement->isApplicable($item)) {
                return false;
            }
        }

        return true;
    }
}