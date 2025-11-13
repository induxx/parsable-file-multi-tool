<?php

namespace Misery\Component\Source;

use Misery\Component\Common\Cursor\CachedCursor;
use Misery\Component\Common\Cursor\CursorInterface;
use Misery\Component\Item\Processor\NullProcessor;
use Misery\Component\Item\Processor\ProcessorInterface;
use Misery\Component\Reader\ItemReader;
use Misery\Component\Reader\ItemReaderInterface;
use Misery\Component\Reader\Index\Storage\Adapter\RedisHashClientFactory;
use Misery\Component\Reader\Index\Storage\ArrayIdentifierIndexStorage;
use Misery\Component\Reader\Index\Storage\IdentifierIndexStorageInterface;
use Misery\Component\Reader\Index\Storage\RedisIdentifierIndexStorage;

class Source
{
    private $cursor;
    private $alias;
    private $processorIn;
    private $processorOut;
    private $cache;
    private static array $trackedIdentifierStorages = [];
    private static bool $identifierFlushRegistered = false;
    private static ?string $identifierRunKey = null;

    public function __construct(
        CursorInterface $cursor,
        ProcessorInterface $processorIn,
        ProcessorInterface $processorOut,
        string $alias
    ) {
        $this->alias = $alias;
        $this->cursor = $cursor;
        $this->processorIn = $processorIn;
        $this->processorOut = $processorOut;
    }

    public static function createSimple(CursorInterface $cursor, string $alias): self
    {
        return new self($cursor, new NullProcessor(), new NullProcessor(), $alias);
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function encode(array $item): array
    {
        return $this->processorIn->process($item);
    }

    public function decode(array $item): array
    {
        return $this->processorOut->process($item);
    }

    public function getCursor(): CursorInterface
    {
        return $this->cursor;
    }

    public function getReader(): ItemReaderInterface
    {
        return new ItemReader(clone $this->cursor);
    }

    public function getCachedReader(array $options = []): ItemReaderInterface
    {
        if (null === $this->cache) {
            $identifierConfig = $options['identifier'] ?? null;
            unset($options['identifier']);

            $cursorOptions = array_merge(['cache_size' => CachedCursor::LARGE_CACHE_SIZE], $options);

            $reader = new ItemReader(new CachedCursor(
                clone $this->cursor,
                $cursorOptions
            ));

            if (is_array($identifierConfig) && isset($identifierConfig['field'])) {
                $indexOptions = $identifierConfig['options'] ?? [];
                $indexOptions['sort_before_index'] = $indexOptions['sort_before_index'] ?? true;

                $storage = $this->createIdentifierStorage($identifierConfig);

                $reader = $reader->withIdentifierIndex(
                    $identifierConfig['field'],
                    $storage,
                    $indexOptions
                );
            }

            $this->cache = $reader;
        }

        return $this->cache;
    }

    public static function flushIdentifierStorages(): void
    {
        foreach (self::$trackedIdentifierStorages as $storage) {
            try {
                $storage->clear();
            } catch (\Throwable $exception) {
                // Swallow cleanup issues so shutdown handlers don't break.
            }
        }

        self::$trackedIdentifierStorages = [];
    }

    public static function resetIdentifierRuntimeState(): void
    {
        self::flushIdentifierStorages();
        self::$identifierRunKey = null;
        self::$identifierFlushRegistered = false;
    }

    private function createIdentifierStorage(array $identifierConfig): IdentifierIndexStorageInterface
    {
        $backend = strtolower((string) ($identifierConfig['backend'] ?? 'memory'));

        if ($backend === 'redis' || isset($identifierConfig['redis_url']) || isset($_ENV['REDIS_URL']) || isset($_SERVER['REDIS_URL'])) {
            $storage = $this->createRedisIdentifierStorage($identifierConfig);
            if (null !== $storage) {
                $this->trackIdentifierStorage($storage);

                return $storage;
            }
        }

        return new ArrayIdentifierIndexStorage();
    }

    private function createRedisIdentifierStorage(array $identifierConfig): ?IdentifierIndexStorageInterface
    {
        $redisDsn = $identifierConfig['redis_url']
            ?? $_ENV['REDIS_URL'] ?? $_SERVER['REDIS_URL'] ?? null;

        $clientFactory = $identifierConfig['client_factory'] ?? null;

        try {
            $client = RedisHashClientFactory::createFromDsn($redisDsn, $clientFactory);
        } catch (\Throwable $exception) {
            return null;
        }

        if (null === $client) {
            return null;
        }

        $hashNamespace = $identifierConfig['redis_hash']
            ?? $_ENV['REDIS_INDEX_HASH'] ?? $_SERVER['REDIS_INDEX_HASH'] ?? 'misery:item-reader:index';

        $hashKey = sprintf(
            '%s:%s:%s',
            rtrim($hashNamespace, ':'),
            $this->resolveIdentifierMasterKey($identifierConfig),
            $this->alias
        );

        return new RedisIdentifierIndexStorage($client, $hashKey);
    }

    private function trackIdentifierStorage(IdentifierIndexStorageInterface $storage): void
    {
        if (!self::$identifierFlushRegistered) {
            register_shutdown_function([self::class, 'flushIdentifierStorages']);
            self::$identifierFlushRegistered = true;
        }

        self::$trackedIdentifierStorages[spl_object_id($storage)] = $storage;
    }

    private function resolveIdentifierMasterKey(array $identifierConfig): string
    {
        if (!empty($identifierConfig['master_key'])) {
            return (string) $identifierConfig['master_key'];
        }

        $envKey = $_ENV['MISERY_RUN_KEY'] ?? $_SERVER['MISERY_RUN_KEY'] ?? null;
        if (is_string($envKey) && $envKey !== '') {
            self::$identifierRunKey = $envKey;
        }

        if (null === self::$identifierRunKey) {
            self::$identifierRunKey = bin2hex(random_bytes(10));
            $_ENV['MISERY_RUN_KEY'] = self::$identifierRunKey;
            $_SERVER['MISERY_RUN_KEY'] = self::$identifierRunKey;
        }

        return self::$identifierRunKey;
    }
}
