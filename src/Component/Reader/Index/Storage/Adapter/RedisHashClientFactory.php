<?php

namespace Misery\Component\Reader\Index\Storage\Adapter;

use Misery\Component\Reader\Index\Storage\RedisHashClientInterface;

final class RedisHashClientFactory
{
    public static function createFromDsn(?string $dsn, ?callable $clientFactory = null): ?RedisHashClientInterface
    {
        $dsn = $dsn !== null ? trim($dsn) : null;
        if (empty($dsn)) {
            return null;
        }

        $configuration = self::parseDsn($dsn);

        if (null === $clientFactory) {
            if (!class_exists(\Redis::class)) {
                return null;
            }

            $clientFactory = static fn (): object => new \Redis();
        }

        $client = $clientFactory();

        if (!\is_object($client) || !method_exists($client, 'connect')) {
            throw new \InvalidArgumentException('Provided Redis client must expose a connect() method.');
        }

        if (null !== $configuration['ssl']) {
            // phpredis treats the 7th argument as a stream context array for TLS connections.
            $client->connect(
                $configuration['host'],
                $configuration['port'],
                $configuration['timeout'],
                null,
                0,
                0,
                ['ssl' => $configuration['ssl']]
            );
        } else {
            $client->connect($configuration['host'], $configuration['port'], $configuration['timeout']);
        }

        if (null !== $configuration['password']) {
            if (null !== $configuration['username']) {
                $client->auth([$configuration['username'], $configuration['password']]);
            } else {
                $client->auth($configuration['password']);
            }
        }

        if (null !== $configuration['database']) {
            $client->select($configuration['database']);
        }

        return new PhpRedisHashClient($client);
    }

    /**
     * @return array{
     *     host: string,
     *     port: int,
     *     timeout: float,
     *     database: int|null,
     *     username: string|null,
     *     password: string|null,
     *     ssl: array|null
     * }
     */
    private static function parseDsn(string $dsn): array
    {
        $parts = parse_url($dsn);

        if (false === $parts || !isset($parts['host'])) {
            throw new \InvalidArgumentException(sprintf('Invalid Redis DSN "%s".', $dsn));
        }

        parse_str($parts['query'] ?? '', $query);

        $database = null;
        if (isset($parts['path'])) {
            $trimmed = ltrim($parts['path'], '/');
            if ($trimmed !== '') {
                $database = (int) $trimmed;
            }
        } elseif (isset($query['db'])) {
            $database = (int) $query['db'];
        }

        $username = $parts['user'] ?? ($query['username'] ?? null);
        $password = $parts['pass'] ?? ($query['password'] ?? null);

        $timeout = isset($query['timeout']) ? (float) $query['timeout'] : 2.0;

        $isTls = ($parts['scheme'] ?? 'redis') === 'rediss';
        $ssl = null;
        if ($isTls) {
            $ssl = [
                'verify_peer' => filter_var($query['verify_peer'] ?? false, FILTER_VALIDATE_BOOL),
                'verify_peer_name' => filter_var($query['verify_peer_name'] ?? false, FILTER_VALIDATE_BOOL),
            ];
        }

        return [
            'host' => $parts['host'],
            'port' => (int) ($parts['port'] ?? 6379),
            'timeout' => $timeout,
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'ssl' => $ssl,
        ];
    }
}
