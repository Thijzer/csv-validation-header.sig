<?php

namespace Misery\Component\Statement;

interface StatementInterface
{
    public function apply(array $item): array;
    public function isApplicable(array $item): bool;
}