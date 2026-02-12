<?php

namespace Tests\Misery\Component\Cursor;

use Misery\Component\Common\Cursor\CursorInterface;

class ArrayCursor implements CursorInterface
{
    /** @var array */
    private $items;
    /** @var array */
    private $keys;
    /** @var int */
    private $index = 0;

    public function __construct(array $items)
    {
        $this->items = $items;
        $this->keys = array_keys($items);
    }

    public function loop(callable $callable): void
    {
        while ($this->valid()) {
            $callable($this->current());
            $this->next();
        }
        $this->rewind();
    }

    public function getIterator(): \Generator
    {
        while ($this->valid()) {
            yield $this->key() => $this->current();
            $this->next();
        }
        $this->rewind();
    }

    public function current(): mixed
    {
        return $this->valid() ? $this->items[$this->keys[$this->index]] : false;
    }

    public function next(): void
    {
        ++$this->index;
    }

    public function key(): mixed
    {
        return $this->valid() ? $this->keys[$this->index] : null;
    }

    public function valid(): bool
    {
        return $this->index < count($this->keys);
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function seek($pointer): void
    {
        $position = array_search($pointer, $this->keys, true);
        $this->index = $position === false ? count($this->keys) : $position;
    }

    public function count(): int
    {
        return count($this->keys);
    }

    public function clear(): void
    {
        $this->items = [];
        $this->keys = [];
        $this->index = 0;
    }
}
