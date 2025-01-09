<?php

namespace Misery\Component\Statement;

use Misery\Component\Action\SetValueAction;

class StatementBuilder
{
    public static function buildFromOperator(string $operator, array $context = []): StatementInterface
    {
        switch ($operator) {
            case 'IN_LIST':
                $statement = InListStatement::prepare(new SetValueAction(), $context);
                break;
            case 'NOT_IN_LIST':
                $statement = NotInListStatement::prepare(new SetValueAction(), $context);
                break;
            case 'IS_NUMERIC':
                $statement = IsNumericsStatement::prepare(new SetValueAction(), $context);
                break;
            case 'IS_NOT_NUMERIC':
                $statement = IsNotNumericsStatement::prepare(new SetValueAction(), $context);
                break;
            case '==':
            case 'EQUALS':
            case null:
                $statement = EqualsStatement::prepare(new SetValueAction());
                break;
            case '>=':
            case 'GREATER_THAN_OR_EQUAL_TO':
                $statement = GreaterThanOrEqualStatement::prepare(new SetValueAction());
                break;
            case '>':
            case 'GREATER_THAN':
                $statement = GreaterThanStatement::prepare(new SetValueAction());
                break;
            case '<=':
            case 'SMALLER_THAN_OR_EQUAL_TO':
                $statement = SmallerThanOrEqualStatement::prepare(new SetValueAction());
                break;
            case '<':
            case 'SMALLER_THAN':
                $statement = SmallerThanStatement::prepare(new SetValueAction());
                break;
            case '!=':
            case 'NOT_EQUAL':
                $statement = NotEqualStatement::prepare(new SetValueAction());
                break;
            case 'CONTAINS':
                $statement = ContainsStatement::prepare(new SetValueAction());
                break;
            case StartsWithStatement::NAME:
                $statement = StartsWithStatement::prepare(new SetValueAction());
                break;
            case 'EMPTY':
                $statement = EmptyStatement::prepare(new SetValueAction());
                break;
            case 'NOT_EMPTY':
                $statement = NotEmptyStatement::prepare(new SetValueAction());
                break;
            case 'DATE':
                $statement = DateStatement::prepare(new SetValueAction());
                break;
            case 'REGEX':
                $statement = RegexStatement::prepare(new SetValueAction());
                break;
            case 'CHARLEN_GT':
                $context['condition'] = 'greater_than';
                $statement = CharlenStatement::prepare(new SetValueAction(), $context);

                break;
            default:
                throw new \Exception('invalid statement operator');
        }

        return $statement;
    }

    public static function build($when, array $context = []): StatementInterface
    {
        $statement = null;

        if (is_string($when)) {
            $statement = static::fromExpression($when, $context);
            if ($statement instanceof StatementCollection) {
                return new CollectionStatement($statement, new SetValueAction(), $context);
            }
        }
        if (is_array($when)) {
            $operator = $when['operator'] ?? null;
            $context = array_merge($when['context'] ?? [], $context);

            if ($operator) {
                $statement = self::buildFromOperator($operator, $context);
                $statement->when($when['field'], $when['state'] ?? null);
            }
        }

        if (null === $statement) {
            throw new \RuntimeException('Unprocessable WHEN field');
        }

        return $statement;
    }

    private static function fromExpression(string $whenString, array $context): StatementInterface
    {
        $andFields = explode(' AND ', $whenString) ?? [];
        if (count($andFields) > 1) {
            $collection = new StatementCollection();
            foreach ($andFields as $i => $andField) {
                $fields = explode(' ', $andField);
                $statement = self::buildFromOperator($fields[1], $context);
                $statement->when($fields[0], $fields[2] ?? null);
                $collection->add($statement);
            }
            return $collection;
        }

        $statement = EqualsStatement::prepare(new SetValueAction());

        $orFields = explode(' OR ', $whenString) ?? [];
        if (count($orFields) === 2) {
            $fields = explode(' == ', $orFields[0]);
            $statement->when($fields[0], $fields[1]);
            $fields = explode(' == ', $orFields[1]);
            $statement->or($fields[0], $fields[1]);
        }

        $containsFields = explode(' CONTAINS ', $whenString) ?? [];
        if (count($containsFields) === 2) {
            $statement = ContainsStatement::prepare(new SetValueAction());
            $statement->when($containsFields[0], $containsFields[1]);

            return $statement;
        }

        if (count($andFields) === 1) {
            $fields = explode(' ', $whenString);
            $statement = self::buildFromOperator($fields[1], $context);
            $statement->when($fields[0], $fields[2] ?? null);
        }

        return $statement;
    }

    public static function fromArray(array $whenArray, array $context = []): array
    {
        $statements = [];
        foreach ($whenArray as $when) {
            $statements[] = self::build($when);
        }

        return $statements;
    }
}