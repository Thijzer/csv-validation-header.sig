<?php

namespace Misery\Component\Akeneo\DataStructure;

use Misery\Model\DataStructure\ScopeInterface;

class AkeneoScope implements ScopeInterface
{
    private ?string $channelCode;
    private ?string $localeCode;

    public function __construct(?string $channelCode = null, ?string $localeCode = null)
    {
        $this->channelCode = $channelCode;
        $this->localeCode = $localeCode;
    }

    public static function fromArray(array $data): self
    {
        $self = new self();
        $self->channelCode = $data['scope'] ?? $data['channel_code'] ?? $data['channel'] ?? null;
        $self->localeCode =  $data['locale'] ?? $data['locale_code'] ?? $data['scope_locale'] ?? null;

        return $self;
    }

    public function equals(ScopeInterface $scope): bool
    {
        return
            $this->getChannel() === $scope->getChannel() &&
            $this->getLocale() === $scope->getLocale()
            ;
    }

    public function getChannel(): ?string
    {
        return $this->channelCode;
    }

    public function getLocale(): ?string
    {
        return $this->localeCode;
    }

    public function isLocalizable(): bool
    {
        return $this->localeCode !== null;
    }

    public function isScopable(): bool
    {
        return $this->channelCode !== null;
    }

    public function toArray(): array
    {
        return [
            'channel' => $this->channelCode,
            'locale' => $this->localeCode,
        ];
    }

    public function __toString(): string
    {
        return implode('|', array_values($this->toArray()));
    }
}