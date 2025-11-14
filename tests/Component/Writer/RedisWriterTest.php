<?php

namespace Tests\Misery\Component\Writer;

use Misery\Component\Common\Redis\RedisItemBuffer;
use Misery\Component\Writer\RedisWriter;
use PHPUnit\Framework\TestCase;
use Tests\Misery\Component\Reader\Index\Storage\InMemoryRedisHashClient;

final class RedisWriterTest extends TestCase
{
    public function testWritesItemsIntoRedisBuffer(): void
    {
        $buffer = new RedisItemBuffer(new InMemoryRedisHashClient(), 'buffer:test');
        $writer = new RedisWriter($buffer);

        $writer->write(['sku' => 'SKU-1']);
        $writer->write(['sku' => 'SKU-2']);
        $writer->close();

        $this->assertSame(
            [
                ['sku' => 'SKU-1'],
                ['sku' => 'SKU-2'],
            ],
            $buffer->toArray()
        );
    }
}
