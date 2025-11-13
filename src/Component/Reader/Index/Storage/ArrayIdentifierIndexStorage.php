<?php

namespace Misery\Component\Reader\Index\Storage;

final class ArrayIdentifierIndexStorage implements IdentifierIndexStorageInterface
{
    /**
     * @var array<string, array<int, array<string, mixed>>>
     */
    private array $payload = [];

    public function hydrate(array $payload): void
    {
        $this->payload = $payload;
    }

    public function fetch(string $identifier): ?array
    {
        return $this->payload[$identifier] ?? null;
    }

    public function fetchMany(array $identifiers): array
    {
        $results = [];
        foreach ($identifiers as $identifier) {
            if (isset($this->payload[$identifier])) {
                $results[$identifier] = $this->payload[$identifier];
            }
        }

        return $results;
    }

    public function all(): iterable
    {
        foreach ($this->payload as $identifier => $rows) {
            yield $identifier => $rows;
        }
    }

    public function clear(): void
    {
        $this->payload = [];
    }

    public function count(): int
    {
        return \count($this->payload);
    }
}
