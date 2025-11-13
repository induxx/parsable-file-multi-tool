<?php

namespace Misery\Component\Reader\Index\Storage;

/**
 * Storage abstraction for identifier indexes so we can plug different backends
 * (in-memory, Redis, database, ...) without touching the reader logic.
 */
interface IdentifierIndexStorageInterface
{
    /**
     * Replace currently stored entries with the provided payload.
     *
     * The payload is an associative array keyed by identifier value where every
     * value holds a list of hits. Each hit is expected to be an associative array
     * with, at minimum, an `item` key. Additional metadata (like `position`) is
     * allowed so callers can keep track of the original cursor pointer.
     *
     * @param array<string, array<int, array<string, mixed>>> $payload
     */
    public function hydrate(array $payload): void;

    /**
     * @return array<int, array<string, mixed>>|null
     */
    public function fetch(string $identifier): ?array;

    /**
     * @param string[] $identifiers
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function fetchMany(array $identifiers): array;

    /**
     * @return iterable<string, array<int, array<string, mixed>>>
     */
    public function all(): iterable;

    public function clear(): void;

    public function count(): int;
}
