<?php

namespace Tests\Misery\Component\Cursor;

use Misery\Component\Common\Cursor\RedisCachedCursor;
use PHPUnit\Framework\TestCase;
use Tests\Misery\Component\Cache\ArrayCache;

class RedisCachedCursorTest extends TestCase
{
    public function test_it_caches_with_expected_prefix_and_identifier(): void
    {
        $cache = new ArrayCache();
        $cursor = new RedisCachedCursor(
            new ArrayCursor([0, false, '', null, 'ok']),
            $cache,
            'my-source',
            ['cache_size' => 2],
            'todo-build'
        );

        $result = [];
        foreach ($cursor->getIterator() as $item) {
            $result[] = $item;
        }

        $this->assertSame([0, false, '', null, 'ok'], $result);

        $keys = $cache->getKeys();
        $this->assertNotEmpty($keys);
        $this->assertStringStartsWith('/TRANSFORMATION/todo-build/my-source/chunk:', $keys[0]);
    }

    public function test_it_clears_cached_chunks_on_request(): void
    {
        $cache = new ArrayCache();
        $cursor = new RedisCachedCursor(
            new ArrayCursor([1, 2, 3, 4]),
            $cache,
            'my-source',
            ['cache_size' => 2],
            'todo-build'
        );

        foreach ($cursor->getIterator() as $item) {
            $item + 1;
        }

        $this->assertNotEmpty($cache->getKeys());
        $cursor->clearCacheEntries();

        $this->assertSame([], $cache->getKeys());
    }
}
