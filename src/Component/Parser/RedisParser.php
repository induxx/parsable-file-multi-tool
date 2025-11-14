<?php

namespace Misery\Component\Parser;

use Misery\Component\Common\Redis\RedisItemBuffer;
use Misery\Component\Reader\ItemCollection;

final class RedisParser extends ItemCollection
{
    private RedisItemBuffer $buffer;

    public function __construct(RedisItemBuffer $buffer)
    {
        $this->buffer = $buffer;
        parent::__construct($buffer->toArray());
    }

    public static function create(RedisItemBuffer $buffer): self
    {
        return new self($buffer);
    }

    public function clear(): void
    {
        $this->buffer->clear();
        parent::clear();
    }

    public function getBuffer(): RedisItemBuffer
    {
        return $this->buffer;
    }
}
