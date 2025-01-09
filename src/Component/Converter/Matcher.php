<?php

namespace Misery\Component\Converter;

class Matcher
{
    private string $separator = '|';
    private array $matches = [];
    private ?string $scope = null;
    private ?string $locale = null;

    public static function create(string $mainMatch, ?string $locale = null, ?string $scope = null): self
    {
        $main = new self();
        $main->locale = $locale;
        $main->scope = $scope;

        $matches = explode($main->separator, $mainMatch);
        if ($main->isLocalizable()) {
            $matches[] = $locale;
        }
        if ($main->isScopable()) {
            $matches[] = $scope;
        }
        $main->matches = $matches;

        return $main;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function isLocalizable(): bool
    {
        return is_string($this->locale);
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function isScopable(): bool
    {
        return is_string($this->scope);
    }

    public function getPrimaryKey(): string
    {
        return $this->matches[1] ?? $this->matches[0];
    }

    public function isProperty(): bool
    {
        return count($this->matches) === 1;
    }

    public function getRowKey(): string
    {
        return str_replace('values-', '', str_replace($this->separator, '-', $this->getMainKey()));
    }

    public function getMainKey(): string
    {
        return implode($this->separator, $this->matches);
    }

    public function matches(string $match): bool
    {
        return in_array($match, $this->matches) || $match === $this->getMainKey();
    }

    public function duplicateWithNewKey(string $newPrimaryKey): self
    {
        $matcher = new self();
        $matcher->scope = $this->scope;
        $matcher->locale = $this->locale;
        $matcher->matches = $this->matches;
        $matcher->matches[1] = $newPrimaryKey;
        if (count(explode($this->separator, $newPrimaryKey)) > 1) {
            $matcher->matches = explode($this->separator, $newPrimaryKey);
        }

        // obelink-xml-v3
        //$matcher->matches = explode($this->separator, $newPrimaryKey);

        return $matcher;
    }
}