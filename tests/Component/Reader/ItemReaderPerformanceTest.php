<?php

namespace Tests\Misery\Component\Reader;

use Misery\Component\Common\Tracker\TimeTracker;
use Misery\Component\Reader\ItemCollection;
use Misery\Component\Reader\ItemReader;
use PHPUnit\Framework\TestCase;

class ItemReaderPerformanceTest extends TestCase
{
    /** @group performance */
    public function test_find_performance(): void
    {
        $items = [];
        foreach (range(1, 50000) as $i) {
            $items[] = [
                'id' => (string) $i,
                'group' => $i % 2 === 0 ? 'A' : 'B',
                'value' => 'v' . $i,
            ];
        }

        $reader = new ItemReader(new ItemCollection($items));

        $tracker = new TimeTracker();
        foreach (range(1, 5) as $i) {
            foreach ($reader->find([
                'group' => ['A'],
                'value' => 'NOT_EMPTY',
                'id' => 'UNIQUE',
            ])->getIterator() as $item) {
                $item['id'];
            }
        }
        $elapsed = $tracker->check();

        $this->assertLessThan(5.0, $elapsed);
    }
}
