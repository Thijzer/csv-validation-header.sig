<?php

namespace Misery\Model\DataStructure;

interface ScopeInterface
{
    public function equals(ScopeInterface $scope): bool;

    public function getChannel(): ?string;

    public function getLocale(): ?string;

    public function isLocalizable(): bool;

    public function isScopable(): bool;
}