<?php

namespace Tests\Misery\Component\Cursor;

use Misery\Component\Common\Cursor\CachedCursor;
use Misery\Component\Common\Tracker\TimeTracker;
use PHPUnit\Framework\TestCase;

class CachedCursorPerformanceTest extends TestCase
{
    /** @group performance */
    public function test_cached_cursor_iteration_performance(): void
    {
        $items = [];
        foreach (range(1, 50000) as $i) {
            $items[] = $i;
        }

        $cursor = new CachedCursor(new ArrayCursor($items), ['cache_size' => 1000]);

        $tracker = new TimeTracker();
        foreach (range(1, 20) as $i) {
            foreach ($cursor->getIterator() as $item) {
                $item + 1;
            }
        }
        $elapsed = $tracker->check();

        $this->assertLessThan(2.5, $elapsed);
    }
}
