<?php

namespace Tests\Misery\Component\Reader\Index\Storage;

use Misery\Component\Reader\Index\Storage\RedisIdentifierIndexStorage;
use PHPUnit\Framework\TestCase;

class RedisIdentifierIndexStorageTest extends TestCase
{
    public function test_it_hydrates_and_fetches_rows(): void
    {
        $client = new InMemoryRedisHashClient();
        $storage = new RedisIdentifierIndexStorage($client, 'test:index');

        $payload = [
            'A' => [[
                'item' => ['id' => 'A', 'label' => 'Foo'],
                'position' => 0,
            ]],
            'B' => [[
                'item' => ['id' => 'B', 'label' => 'Bar'],
                'position' => 1,
            ]],
        ];

        $storage->hydrate($payload);

        $this->assertSame($payload['A'], $storage->fetch('A'));
        $this->assertSame($payload, $storage->fetchMany(['A', 'B']));

        $all = iterator_to_array($storage->all());
        $this->assertSame($payload, $all);

        $storage->clear();
        $this->assertSame([], iterator_to_array($storage->all()));
    }
}
