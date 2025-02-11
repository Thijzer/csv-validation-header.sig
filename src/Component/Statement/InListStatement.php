<?php

namespace Misery\Component\Statement;

class InListStatement implements PredeterminedStatementInterface
{
    public const NAME = 'IN_LIST';

    use StatementTrait;

    private function whenField(Field $field, array $item): bool
    {
        return
            array_key_exists($field->getField(), $item) &&
            is_string($item[$field->getField()]) &&
            isset($this->context['list']) &&
            is_array($this->context['list']) &&
            (
                in_array($item[$field->getField()], $this->context['list']) ||
                array_key_exists($item[$field->getField()], $this->context['list'])
            )
        ;
    }
}