<?php

namespace Tests\Misery\Component\Common\Redis;

use Misery\Component\Common\Redis\RedisItemBuffer;
use PHPUnit\Framework\TestCase;
use Tests\Misery\Component\Reader\Index\Storage\InMemoryRedisHashClient;

final class RedisItemBufferTest extends TestCase
{
    public function testAppendsAndIteratesInInsertionOrder(): void
    {
        $client = new InMemoryRedisHashClient();
        $buffer = new RedisItemBuffer($client, 'buffer:test');

        $buffer->append(['sku' => 'SKU-1']);
        $buffer->append(['sku' => 'SKU-2']);

        $this->assertSame(2, $buffer->count());
        $this->assertSame(
            [
                ['sku' => 'SKU-1'],
                ['sku' => 'SKU-2'],
            ],
            $buffer->toArray()
        );

        $buffer->clear();

        $this->assertSame(0, $buffer->count());
    }
}
