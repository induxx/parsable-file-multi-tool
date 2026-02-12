<?php

namespace Tests\Misery\Component\Cursor;

use Misery\Component\Common\Cursor\CachedCursor;
use PHPUnit\Framework\TestCase;

class CachedCursorTest extends TestCase
{
    public function test_it_keeps_falsy_values_across_cache_windows(): void
    {
        $items = [0, false, '', null, 'ok'];
        $cursor = new CachedCursor(new ArrayCursor($items), ['cache_size' => 2]);

        $result = [];
        foreach ($cursor->getIterator() as $item) {
            $result[] = $item;
        }

        $this->assertSame($items, $result);
    }
}
