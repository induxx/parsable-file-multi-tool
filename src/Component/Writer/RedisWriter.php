<?php

namespace Misery\Component\Writer;

use Misery\Component\Common\Redis\RedisItemBuffer;

final class RedisWriter implements ItemWriterInterface
{
    private RedisItemBuffer $buffer;

    public function __construct(RedisItemBuffer $buffer)
    {
        $this->buffer = $buffer;
    }

    public function write(array $data): void
    {
        $this->buffer->append($data);
    }

    public function close(): void
    {
        // nothing to close for Redis writes
    }

    public function getBuffer(): RedisItemBuffer
    {
        return $this->buffer;
    }
}
