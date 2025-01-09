<?php

namespace Misery\Component\Statement;

class DateStatement implements PredeterminedStatementInterface
{
    public const NAME = 'DATE';

    use StatementTrait;

    /*
     * IMPORTANT: Use date_time action first when date is not a unix timestamp
     */

    private function whenField(Field $field, array $item): bool
    {
        if (isset($item[$field->getField()])) {
            switch ($field->getValue()) {
                case 'TODAY':
                    return date('Y-m-d', strtotime($item[$field->getField()])) === date('Y-m-d');
                case 'YESTERDAY':
                    return date('Y-m-d', strtotime($item[$field->getField()])) === date('Y-m-d', strtotime('-1 day'));
                case 'TOMORROW':
                    return date('Y-m-d', strtotime($item[$field->getField()])) === date('Y-m-d', strtotime('+1 day'));
                case 'PAST':
                case '<':
                    return date('Y-m-d H:i:s', strtotime($item[$field->getField()])) < date('Y-m-d H:i:s');
                case '<=':
                    return date('Y-m-d H:i:s', strtotime($item[$field->getField()])) <= date('Y-m-d H:i:s');
                case 'FUTURE':
                case '>':
                    return date('Y-m-d H:i:s', strtotime($item[$field->getField()])) > date('Y-m-d H:i:s');
                case '>=':
                    return date('Y-m-d H:i:s', strtotime($item[$field->getField()])) >= date('Y-m-d H:i:s');
                case '=':
                default:
                    return $item[$field->getField()] === $item[$field->getValue()];
            }
        }
        return false;
    }
}