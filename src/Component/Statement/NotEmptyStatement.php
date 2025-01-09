<?php

namespace Misery\Component\Statement;

class NotEmptyStatement implements PredeterminedStatementInterface
{
    public const NAME = 'NOT_EMPTY';

    use StatementTrait;

    private function whenField(Field $field, array $item): bool
    {
        if (is_array($item[$field->getField()])) {
            return array_filter($item[$field->getField()]) !== [];
        }

        return
            isset($item[$field->getField()]) &&
            false === empty($item[$field->getField()])
        ;
    }
}