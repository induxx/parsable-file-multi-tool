<?php

namespace Tests\Misery\Component\Source;

use Misery\Component\Item\Processor\NullProcessor;
use Misery\Component\Parser\CsvParser;
use Misery\Component\Reader\ItemCollection;
use Misery\Component\Reader\ItemReaderInterface;
use Misery\Component\Source\Source;
use PHPUnit\Framework\TestCase;
use Tests\Misery\Component\Reader\Index\Storage\Adapter\FakeRedisClient;

class SourceTest extends TestCase
{
    public function test_init_source(): void
    {
        $file = __DIR__ . '/../../examples/users.csv';

        $parser = CsvParser::create($file);
        $current = $parser->current();

        $source = new Source(
            clone $parser,
            new NullProcessor(),
            new NullProcessor(),
            'products'
        );

        $this->assertInstanceOf(ItemReaderInterface::class, $source->getReader());

        $this->assertSame($source->getReader()->read(), $current);
    }

    public function test_cached_reader_can_build_identifier_index_with_redis_factory(): void
    {
        Source::resetIdentifierRuntimeState();

        $collection = new ItemCollection([
            ['id' => '1', 'value' => 'foo'],
            ['id' => '2', 'value' => 'bar'],
        ]);

        $source = Source::createSimple($collection, 'products');

        $client = null;
        $reader = $source->getCachedReader([
            'identifier' => [
                'field' => 'id',
                'redis_url' => 'redis://redis:6379/0',
                'backend' => 'redis',
                'client_factory' => static function () use (&$client) {
                    return $client = new FakeRedisClient();
                },
            ],
        ]);

        $items = $reader->find(['id' => '2'])->getItems();

        $this->assertCount(1, $items);
        $this->assertSame('bar', current($items)['value']);
        $this->assertNotNull($client);
        $this->assertSame('Redis Reader|Writer', $reader->getIdentifierBackendLabel());

        $_ENV['MISERY_RUN_KEY'] = 'test-run';
        $_SERVER['MISERY_RUN_KEY'] = 'test-run';
        Source::resetIdentifierRuntimeState();

        $source = Source::createSimple($collection, 'products');
        $reader = $source->getCachedReader([
            'identifier' => [
                'field' => 'id',
                'redis_url' => 'redis://redis:6379/0',
                'backend' => 'redis',
                'client_factory' => static function () use (&$client) {
                    return $client = new FakeRedisClient();
                },
            ],
        ]);

        $reader->find(['id' => '1'])->getItems();

        $hashKey = 'misery:item-reader:index:test-run:products';
        $this->assertSame(2, $client->hLen($hashKey));

        Source::flushIdentifierStorages();
        $this->assertSame([], $client->dumpHash($hashKey));

        unset($_ENV['MISERY_RUN_KEY'], $_SERVER['MISERY_RUN_KEY']);
    }
}
