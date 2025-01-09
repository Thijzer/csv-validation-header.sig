<?php

namespace Misery\Component\Statement;

use Misery\Component\Action\ActionInterface;

interface PredeterminedStatementInterface extends StatementInterface
{
    public static function prepare(ActionInterface $action, array $context = []): StatementInterface;
    public function when(string $field, string $value = null): StatementInterface;
    public function and(string $field, string $value = null): StatementInterface;
    public function or(string $field, string $value = null): StatementInterface;
    public function then(string $field, $value = null): void;
}