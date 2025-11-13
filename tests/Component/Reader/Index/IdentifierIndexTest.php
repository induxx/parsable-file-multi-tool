<?php

namespace Tests\Misery\Component\Reader\Index;

use Misery\Component\Reader\Index\IdentifierIndex;
use Misery\Component\Reader\Index\Storage\ArrayIdentifierIndexStorage;
use Misery\Component\Reader\Index\Storage\RedisIdentifierIndexStorage;
use Tests\Misery\Component\Reader\Index\Storage\InMemoryRedisHashClient;
use PHPUnit\Framework\TestCase;

class IdentifierIndexTest extends TestCase
{
    public function test_it_matches_identifiers(): void
    {
        $storage = new ArrayIdentifierIndexStorage();
        $index = new IdentifierIndex('id', $storage, ['sort_before_index' => false]);

        $index->prime([
            2 => ['id' => 'b', 'value' => 'second'],
            0 => ['id' => 'a', 'value' => 'first'],
        ]);

        $rows = $index->match('b');

        $this->assertCount(1, $rows);
        $this->assertSame('second', $rows[0]['value']);
        $this->assertSame('Memory Reader|Writer', $index->getBackendLabel());
    }

    public function test_it_sorts_when_requested(): void
    {
        $storage = new ArrayIdentifierIndexStorage();
        $index = new IdentifierIndex('id', $storage, ['sort_before_index' => true]);

        $index->prime([
            0 => ['id' => 'B', 'value' => 'second'],
            1 => ['id' => 'A', 'value' => 'first'],
        ]);

        $rows = $index->allRows();

        $this->assertSame(['A', 'B'], array_column($rows, 'id'));
    }

    public function test_it_labels_redis_backend(): void
    {
        $storage = new RedisIdentifierIndexStorage(new InMemoryRedisHashClient(), 'test');
        $index = new IdentifierIndex('id', $storage);

        $this->assertSame('Redis Reader|Writer', $index->getBackendLabel());
    }
}
