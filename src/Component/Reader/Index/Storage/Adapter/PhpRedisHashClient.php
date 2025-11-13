<?php

namespace Misery\Component\Reader\Index\Storage\Adapter;

use Misery\Component\Reader\Index\Storage\RedisHashClientInterface;

/**
 * Small adapter so we can type against ext-redis without coupling the storage
 * to the actual Redis implementation the application picks.
 */
final class PhpRedisHashClient implements RedisHashClientInterface
{
    /**
     * @var object
     */
    private object $client;

    /**
     * @param object $client Instance of \Redis, \RedisArray, \RedisCluster, ...
     */
    public function __construct(object $client)
    {
        $this->client = $client;
    }

    public function hSet(string $key, string $field, string $value): bool|int
    {
        return $this->client->hSet($key, $field, $value);
    }

    public function hGet(string $key, string $field): string|false
    {
        return $this->client->hGet($key, $field);
    }

    public function hMGet(string $key, array $fields): array
    {
        return $this->client->hMGet($key, $fields);
    }

    public function hGetAll(string $key): array
    {
        return $this->client->hGetAll($key);
    }

    public function hLen(string $key): int
    {
        return $this->client->hLen($key);
    }

    public function del(string $key): int
    {
        return $this->client->del($key);
    }
}
