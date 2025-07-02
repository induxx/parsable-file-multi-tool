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

    public static function fromString(string $scope): self
    {
        $parts = explode('|', $scope);
        $channelCode = null;
        $localeCode = null;

        if (count($parts) === 2) {
            // Classic case: both channel and locale are defined
            [$channelCode, $localeCode] = $parts;
        } elseif (count($parts) === 1 && $parts[0] !== '') {
            $single = $parts[0];

            // Try to detect if it's a locale code (e.g., en_US)
            if (preg_match('/^[a-z]{2}_[A-Z]{2}$/', $single)) {
                $localeCode = $single;
            } else {
                $channelCode = $single;
            }
        }

        return new self($channelCode, $localeCode);
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

    public function equalsLocale(ScopeInterface $scope): bool
    {
        return
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
        if ($this->channelCode === null && $this->localeCode === null) {
            return '';
        }
        if ($this->channelCode === null) {
            return $this->localeCode;
        }
        if ($this->localeCode === null) {
            return $this->channelCode;
        }

        return implode('|', array_values($this->toArray()));
    }
}