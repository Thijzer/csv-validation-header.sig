<?php

namespace Misery\Component\Statement;

class NotInListStatement implements PredeterminedStatementInterface
{
    public const NAME = 'NOT_IN_LIST';

    use StatementTrait;

    private function whenField(Field $field, array $item): bool
    {
        if (isset($item[$field->getField()]) &&
            is_string($item[$field->getField()]) &&
            isset($this->context['list']) &&
            is_array($this->context['list'])) {

            return false === in_array($item[$field->getField()], $this->context['list']);
        }

        return false;
    }
}