<?php

namespace Misery\Component\Statement;

class CharlenStatement implements PredeterminedStatementInterface
{
    public const NAME = 'CHAR_LEN';

    use StatementTrait;

    private function whenField(Field $field, array $item): bool
    {
        if (is_array($item[$field->getField()])) {
            if (count(array_filter($item[$field->getField()])) === 0) {
                return false;
            }

            return 0 < count(array_filter($item[$field->getField()], function ($itemValue) use ($field) {
                    return $this->whenField($field, [$field->getField() => $itemValue]);
                }));
        }

        switch ($this->context['condition']) {
            case 'greater_than':
                return isset($item[$field->getField()]) && (mb_strlen($item[$field->getField()]) > $this->context['char_len']);
                break;
        }

        throw new \Exception('Unknown statement condition set.');
    }
}