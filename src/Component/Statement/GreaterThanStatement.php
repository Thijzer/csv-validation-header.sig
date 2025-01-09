<?php

namespace Misery\Component\Statement;

class GreaterThanStatement implements PredeterminedStatementInterface
{
    public const NAME = 'GREATER_THAN';

    use StatementTrait;

    private function whenField(Field $field, array $item): bool
    {
        $field->repopulateFromItem($item);

        return
            isset($item[$field->getField()]) &&
            is_numeric($item[$field->getField()]) &&
            $item[$field->getField()] > $field->getValue()
        ;
    }
}