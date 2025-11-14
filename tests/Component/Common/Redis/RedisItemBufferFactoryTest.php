<?php

namespace Tests\Misery\Component\Common\Redis;

use Misery\Component\Common\Redis\RedisItemBufferFactory;
use Misery\Component\Common\Runtime\RunKeyResolver;
use PHPUnit\Framework\TestCase;
use Tests\Misery\Component\Reader\Index\Storage\InMemoryRedisHashClient;

final class RedisItemBufferFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        RunKeyResolver::reset();
    }

    public function testCreatesBufferWithProvidedConfiguration(): void
    {
        $buffer = RedisItemBufferFactory::create([
            'client' => new InMemoryRedisHashClient(),
            'channel' => 'api_payloads',
            'redis_hash' => 'misery:test',
            'master_key' => 'run-key',
        ]);

        $this->assertSame('misery:test:run-key:api_payloads', $buffer->getHashKey());
    }

    public function testRequiresChannelConfiguration(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        RedisItemBufferFactory::create([
            'client' => new InMemoryRedisHashClient(),
        ]);
    }
}
