<?php

namespace Tests\Misery\Component\Cache;

use Misery\Component\Common\Cache\SimpleCacheInterface;

class ArrayCache implements SimpleCacheInterface
{
    /** @var array */
    private $items = [];

    public function get($key, $default = null)
    {
        return $this->items[$key] ?? $default;
    }

    public function set($key, $value, $ttl = null): bool
    {
        $this->items[$key] = $value;
        return true;
    }

    public function delete($key): bool
    {
        if (!array_key_exists($key, $this->items)) {
            return false;
        }
        unset($this->items[$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->items = [];
        return true;
    }

    public function getMultiple($keys, $default = null): \Iterator
    {
        foreach ((array) $keys as $key) {
            yield $key => $this->get($key, $default);
        }
    }

    public function setMultiple($values, $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }

    public function deleteMultiple($keys): bool
    {
        $deleted = false;
        foreach ((array) $keys as $key) {
            $deleted = $this->delete($key) || $deleted;
        }
        return $deleted;
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->items);
    }

    public function getKeys(): array
    {
        return array_keys($this->items);
    }
}
