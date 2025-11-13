<?php

namespace Misery\Component\Reader\Index\Storage;

final class RedisIdentifierIndexStorage implements IdentifierIndexStorageInterface
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
        $this->unserializer = $unserializer ?? static fn (string $payload): array => unserialize($payload, ['allowed_classes' => true]);
    }

    public function hydrate(array $payload): void
    {
        $this->clear();

        foreach ($payload as $identifier => $rows) {
            $this->client->hSet($this->hashKey, (string) $identifier, ($this->serializer)($rows));
        }
    }

    public function fetch(string $identifier): ?array
    {
        $encoded = $this->client->hGet($this->hashKey, $identifier);

        if (false === $encoded) {
            return null;
        }

        return ($this->unserializer)($encoded);
    }

    public function fetchMany(array $identifiers): array
    {
        $results = [];
        if ($identifiers === []) {
            return $results;
        }

        $raw = $this->client->hMGet($this->hashKey, $identifiers);

        foreach ($raw as $identifier => $encoded) {
            if (false === $encoded || null === $encoded) {
                continue;
            }
            $results[$identifier] = ($this->unserializer)($encoded);
        }

        return $results;
    }

    public function all(): iterable
    {
        $raw = $this->client->hGetAll($this->hashKey);

        foreach ($raw as $identifier => $encoded) {
            yield $identifier => ($this->unserializer)($encoded);
        }
    }

    public function clear(): void
    {
        $this->client->del($this->hashKey);
    }

    public function count(): int
    {
        return $this->client->hLen($this->hashKey);
    }
}
