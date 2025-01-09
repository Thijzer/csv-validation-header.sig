<?php

namespace Misery\Component\Statement;

class ContainsStatement implements PredeterminedStatementInterface
{
    public const NAME = 'CONTAINS';

    use StatementTrait;

    private function whenField(Field $field, array $item): bool
    {
        return
            isset($item[$field->getField()]) &&
            is_string($item[$field->getField()]) &&
            strpos($item[$field->getField()], $field->getValue()) !== false
        ;
    }
}