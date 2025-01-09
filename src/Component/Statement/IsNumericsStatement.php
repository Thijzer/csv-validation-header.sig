<?php

namespace Misery\Component\Statement;

class IsNumericsStatement implements PredeterminedStatementInterface
{
    public const NAME = 'IS_NUMERIC';

    use StatementTrait;

    private function whenField(Field $field, array $item): bool
    {
        if (is_array($item[$field->getField()])) {
            return count(array_filter($item[$field->getField()], function ($itemValue) use ($field) {
                return $this->whenField($field, [$field->getField() => $itemValue]);
            })) === count(array_filter($item[$field->getField()]));
        }

        return
            isset($item[$field->getField()]) &&
            is_numeric(str_replace([',', '.'], '', $item[$field->getField()]))
        ;
    }
}