<?php

namespace Misery\Model\DataStructure;

use Misery\Component\Converter\Matcher;

interface ItemNodeInterface
{
    public function getContext(): array;
    public function getScope(): ScopeInterface;
    public function equals(string $code): bool;
    public function getMatcher(): Matcher;
    public function getType(): string;
    public function getCode(): string;
    public function getValue();
    public function getDataValue();
}