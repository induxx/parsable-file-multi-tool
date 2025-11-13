<?php

namespace Misery\Component\Reader\Index\Storage;

interface RedisHashClientInterface
{
    public function hSet(string $key, string $field, string $value): bool|int;

    public function hGet(string $key, string $field): string|false;

    /**
     * @param string[] $fields
     *
     * @return array<string, string|false>
     */
    public function hMGet(string $key, array $fields): array;

    /**
     * @return array<string, string>
     */
    public function hGetAll(string $key): array;

    public function hLen(string $key): int;

    public function del(string $key): int;
}
