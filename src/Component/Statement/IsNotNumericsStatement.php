<?php

namespace Misery\Component\Statement;

class IsNotNumericsStatement implements PredeterminedStatementInterface
{
    public const NAME = 'IS_NOT_NUMERIC';

    use StatementTrait;

    private function whenField(Field $field, array $item): bool
    {
        return
            isset($item[$field->getField()]) &&
            false === is_numeric(str_replace(',', '.', $item[$field->getField()]))
        ;
    }
}
