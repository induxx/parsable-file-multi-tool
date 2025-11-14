<?php

namespace Misery\Component\Common\Redis;

use Misery\Component\Reader\Index\Storage\RedisHashClientInterface;

final class RedisItemBuffer
{
    private RedisHashClientInterface $client;
    private string $hashKey;
    /** @var callable */
    private $serializer;
    /** @var callable */
    private $unserializer;

    public function __construct(
        RedisHashClientInterface $client,
        string $hashKey,
        ?callable $serializer = null,
        ?callable $unserializer = null
    ) {
        $this->client = $client;
        $this->hashKey = $hashKey;
        $this->serializer = $serializer ?? static fn (array $payload): string => serialize($payload);
        $this->unserializer = $unserializer ?? static function (string $payload): array {
            $value = unserialize($payload, ['allowed_classes' => true]);

            return \is_array($value) ? $value : [];
        };
    }

    public function append(array $payload): void
    {
        $this->client->hSet($this->hashKey, $this->nextField(), ($this->serializer)($payload));
    }

    public function iterate(): \Generator
    {
        $raw = $this->client->hGetAll($this->hashKey);

        if ($raw === []) {
            return;
        }

        ksort($raw, SORT_STRING);

        foreach ($raw as $encoded) {
            if (!is_string($encoded)) {
                continue;
            }

            yield ($this->unserializer)($encoded);
        }
    }

    public function toArray(): array
    {
        return iterator_to_array($this->iterate(), false);
    }

    public function clear(): void
    {
        $this->client->del($this->hashKey);
    }

    public function count(): int
    {
        return $this->client->hLen($this->hashKey);
    }

    public function getHashKey(): string
    {
        return $this->hashKey;
    }

    private function nextField(): string
    {
        return sprintf('%010d', $this->client->hLen($this->hashKey));
    }
}
