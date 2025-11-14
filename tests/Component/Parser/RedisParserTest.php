<?php

namespace Tests\Misery\Component\Parser;

use Misery\Component\Common\Redis\RedisItemBuffer;
use Misery\Component\Parser\RedisParser;
use PHPUnit\Framework\TestCase;
use Tests\Misery\Component\Reader\Index\Storage\InMemoryRedisHashClient;

final class RedisParserTest extends TestCase
{
    public function testReadsItemsFromRedisBuffer(): void
    {
        $buffer = new RedisItemBuffer(new InMemoryRedisHashClient(), 'buffer:test');
        $buffer->append(['id' => 1, 'payload' => ['foo' => 'bar']]);
        $buffer->append(['id' => 2, 'payload' => ['foo' => 'baz']]);

        $parser = RedisParser::create($buffer);
        $parser->rewind();

        $this->assertTrue($parser->valid());
        $this->assertSame(['id' => 1, 'payload' => ['foo' => 'bar']], $parser->current());

        $parser->next();
        $this->assertTrue($parser->valid());
        $this->assertSame(['id' => 2, 'payload' => ['foo' => 'baz']], $parser->current());

        $parser->next();
        $this->assertFalse($parser->valid());

        $parser->clear();
        $this->assertSame(0, $buffer->count());
    }
}
