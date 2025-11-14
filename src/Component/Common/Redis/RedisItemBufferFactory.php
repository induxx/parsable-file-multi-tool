<?php

namespace Misery\Component\Common\Redis;

use Assert\Assert;
use Misery\Component\Common\Runtime\RunKeyResolver;
use Misery\Component\Reader\Index\Storage\Adapter\RedisHashClientFactory;
use Misery\Component\Reader\Index\Storage\RedisHashClientInterface;

final class RedisItemBufferFactory
{
    public static function create(array $configuration): RedisItemBuffer
    {
        $channel = $configuration['channel'] ?? null;
        Assert::that($channel, 'Redis "channel" configuration must be provided.')
            ->notEmpty()->string();

        $channel = (string) $channel;

        $client = self::resolveClient($configuration);

        $namespace = $configuration['redis_hash']
            ?? $_ENV['REDIS_PAYLOAD_HASH'] ?? $_SERVER['REDIS_PAYLOAD_HASH'] ?? 'misery:item-buffer';

        $namespace = rtrim($namespace, ':');

        $masterKey = RunKeyResolver::resolve($configuration['master_key'] ?? null);

        $hashKey = sprintf('%s:%s:%s', $namespace, $masterKey, $channel);

        $buffer = new RedisItemBuffer(
            $client,
            $hashKey,
            $configuration['serializer'] ?? null,
            $configuration['unserializer'] ?? null
        );

        if (!empty($configuration['clear_before_use'])) {
            $buffer->clear();
        }

        return $buffer;
    }

    private static function resolveClient(array $configuration): RedisHashClientInterface
    {
        if (isset($configuration['client'])) {
            if (!$configuration['client'] instanceof RedisHashClientInterface) {
                throw new \InvalidArgumentException('Provided client must implement RedisHashClientInterface.');
            }

            return $configuration['client'];
        }

        $redisUrl = $configuration['redis_url']
            ?? $_ENV['REDIS_URL'] ?? $_SERVER['REDIS_URL'] ?? null;
        $clientFactory = $configuration['client_factory'] ?? null;

        $client = RedisHashClientFactory::createFromDsn($redisUrl, $clientFactory);

        if (null === $client) {
            throw new \RuntimeException('Redis support is not available. Ensure redis_url or REDIS_URL is configured.');
        }

        return $client;
    }
}
