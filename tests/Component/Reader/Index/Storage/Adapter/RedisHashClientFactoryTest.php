<?php

namespace Tests\Misery\Component\Reader\Index\Storage\Adapter;

use Misery\Component\Reader\Index\Storage\Adapter\RedisHashClientFactory;
use Misery\Component\Reader\Index\Storage\RedisHashClientInterface;
use PHPUnit\Framework\TestCase;

class RedisHashClientFactoryTest extends TestCase
{
    public function test_it_creates_a_client_from_dsn(): void
    {
        $client = null;
        $hashClient = RedisHashClientFactory::createFromDsn(
            'redis://user:secret@redis.test:6380/5',
            function () use (&$client) {
                return $client = new FakeRedisClient();
            }
        );

        $this->assertInstanceOf(RedisHashClientInterface::class, $hashClient);
        $this->assertNotNull($client);
        $this->assertSame([
            ['redis.test', 6380, 2.0],
        ], $client->calls['connect'] ?? []);
        $this->assertSame([[['user', 'secret']]], $client->calls['auth'] ?? []);
        $this->assertSame([ [5] ], $client->calls['select'] ?? []);
    }

    public function test_returns_null_when_no_dsn_provided(): void
    {
        $this->assertNull(RedisHashClientFactory::createFromDsn(null, fn () => new FakeRedisClient()));
        $this->assertNull(RedisHashClientFactory::createFromDsn('  ', fn () => new FakeRedisClient()));
    }
}
