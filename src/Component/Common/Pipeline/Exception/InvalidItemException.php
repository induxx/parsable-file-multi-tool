<?php

namespace Misery\Component\Common\Pipeline\Exception;

use Misery\Component\Akeneo\Client\Errors\AkeneoErrors;

class InvalidItemException extends \Exception
{
    private $invalidItemData;
    private ?AkeneoErrors $errors;
    private $item;
    private mixed $identifier;
    private ?string $identityClass;
    private ?string $method;

    public function __construct(string $message = null, array $invalidItemData, array $item = [])
    {
        $this->invalidItemData = $invalidItemData;

        $this->errors = $this->invalidItemData['errors'] ?? null;
        $this->identifier = $this->invalidItemData['identifier'] ?? null;
        $this->identityClass = $this->invalidItemData['identityClass'] ?? null;
        $this->method = $this->invalidItemData['method'] ?? null;
        unset($this->invalidItemData['errors']);
        unset($this->invalidItemData['identifier']);
        unset($this->invalidItemData['identityClass']);
        unset($this->invalidItemData['method']);

        parent::__construct($message);
        $this->item = $item;
    }

    public function getInvalidIdentifier(): mixed
    {
        return $this->identifier;
    }

    public function getInvalidIdentityClass(): ?string
    {
        return $this->identityClass;
    }

    public function getItem(): array
    {
        return $this->item;
    }

    public function getInvalidItemData(): array
    {
        return $this->invalidItemData;
    }

    public function hasErrors(): bool
    {
        return $this->errors !== null;
    }

    public function getErrors(): AkeneoErrors
    {
        return $this->errors;
    }
}