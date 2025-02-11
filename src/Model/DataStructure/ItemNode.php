<?php

namespace Misery\Model\DataStructure;

use Misery\Component\Akeneo\DataStructure\AkeneoScope;
use Misery\Component\Converter\Matcher;

class ItemNode implements ItemNodeInterface
{
    private Matcher $matcher;
    private string $type = 'generic';

    public function __construct(private readonly string $code, private $value, private readonly AkeneoScope $scope)
    {
        $this->matcher = Matcher::create($this->code, $this->scope->getLocale(), $this->scope->getChannel());
        if ($this->matcher->isProperty()) {
            $this->type = 'property';
        }

        // when dealing with an array, we expect the 'data' property
        if (is_array($this->value) && array_key_exists('data', $this->value)) {

            $this->type = $this->value['type'] ?? 'generic';

            if (isset($this->value['matcher']) && $this->value['matcher'] instanceof Matcher) {
                $this->matcher = $this->value['matcher'];
            }
        }
    }

    public static function withContext(string $code, $value, array $context = []): ItemNode
    {
        $scope = new AkeneoScope();
        if (array_key_exists('locale', $context) && array_key_exists('scope', $context)) {
            $scope = AkeneoScope::fromArray($context);
        }

        return new self($code, $value, $scope);
    }

    public function getContext(): array
    {
        return [
            'locale' => $this->scope->getLocale(),
            'scope' => $this->scope->getChannel(),
            'original-code' => $this->getMatcher()->getPrimaryKey(),
            'code' => $this->code,
        ];
    }

    public function getScope(): ScopeInterface
    {
        return $this->scope;
    }

    public function equals(string $code): bool
    {
        $this->matcher->matches($code);
    }

    public function getMatcher(): Matcher
    {
        return $this->matcher;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getDataValue()
    {
        return $this->value['data'] ?? $this->value ?? null;
    }
}