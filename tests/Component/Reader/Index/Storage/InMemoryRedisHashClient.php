<?php

namespace Tests\Misery\Component\Reader\Index\Storage;

use Misery\Component\Reader\Index\Storage\RedisHashClientInterface;

final class InMemoryRedisHashClient implements RedisHashClientInterface
{
    /** @var array<string, array<string, string>> */
    private array $hashes = [];

    public function hSet(string $key, string $field, string $value): bool|int
    {
        $this->hashes[$key][$field] = $value;

        return 1;
    }

    public function hGet(string $key, string $field): string|false
    {
        return $this->hashes[$key][$field] ?? false;
    }

    public function hMGet(string $key, array $fields): array
    {
        $result = [];
        foreach ($fields as $field) {
            $result[$field] = $this->hashes[$key][$field] ?? false;
        }

        return $result;
    }

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
}
