<?php

namespace Misery\Component\Statement;

class NotEqualStatement implements PredeterminedStatementInterface
{
    public const NAME = 'NOT_EQUAL';

    use StatementTrait;

    private function whenField(Field $field, array $item): bool
    {
        $field->repopulateFromItem($item);

        return
            isset($item[$field->getField()]) &&
            is_string($item[$field->getField()]) &&
            $item[$field->getField()] !== $field->getValue()
        ;
    }
}