<?php

namespace Misery\Component\Statement;

class EqualsStatement implements PredeterminedStatementInterface
{
    public const NAME = 'EQUALS';

    use StatementTrait;

    private function whenField(Field $field, array $item): bool
    {
        $field->repopulateFromItem($item);

        return
            isset($item[$field->getField()]) &&
            is_string($item[$field->getField()]) &&
            $item[$field->getField()] === $field->getValue()
        ;
    }
}