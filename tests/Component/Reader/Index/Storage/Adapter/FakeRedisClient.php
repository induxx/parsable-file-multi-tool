<?php

namespace Tests\Misery\Component\Reader\Index\Storage\Adapter;

/**
 * Lightweight stand-in for the phpredis client so we can exercise Redis-backed
 * logic without the extension.
 */
final class FakeRedisClient
{
    /** @var array<string, array<int, array<int, mixed>>> */
    public array $calls = [];

    /** @var array<string, array<string, string>> */
    private array $hashes = [];

    public function connect(...$arguments): bool
    {
        $this->calls['connect'][] = $arguments;

        return true;
    }

    public function auth($credentials): bool
    {
        $this->calls['auth'][] = [$credentials];

        return true;
    }

    public function select(int $database): bool
    {
        $this->calls['select'][] = [$database];

        return true;
    }

    public function hSet(string $key, string $field, string $value): int
    {
        $this->hashes[$key][$field] = $value;

        return 1;
    }

    public function hGet(string $key, string $field): string|false
    {
        return $this->hashes[$key][$field] ?? false;
    }

    /**
     * @param string[] $fields
     *
     * @return array<string, string|false>
     */
    public function hMGet(string $key, array $fields): array
    {
        $results = [];
        foreach ($fields as $field) {
            $results[$field] = $this->hashes[$key][$field] ?? false;
        }

        return $results;
    }

    /**
     * @return array<string, string>
     */
    public function hGetAll(string $key): array
    {
        return $this->hashes[$key] ?? [];
    }

    public function hLen(string $key): int
    {
        return isset($this->hashes[$key]) ? \count($this->hashes[$key]) : 0;
    }

    public function del(string $key): int
    {
        if (!isset($this->hashes[$key])) {
            return 0;
        }

        unset($this->hashes[$key]);

        return 1;
    }

    /**
     * Helper for assertions.
     */
    public function dumpHash(string $key): array
    {
        return $this->hashes[$key] ?? [];
    }
}
