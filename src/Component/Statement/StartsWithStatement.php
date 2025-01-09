<?php

namespace Misery\Component\Statement;

class StartsWithStatement implements PredeterminedStatementInterface
{
    public const NAME = 'STARTS_WITH';

    use StatementTrait;

    private function whenField(Field $field, array $item): bool
    {
        return
            isset($item[$field->getField()]) &&
            is_string($item[$field->getField()]) &&
            str_starts_with($item[$field->getField()], $field->getValue())
        ;
    }
}