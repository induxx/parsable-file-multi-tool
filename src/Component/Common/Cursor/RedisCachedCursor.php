<?php

namespace Misery\Component\Common\Cursor;

use Misery\Component\Common\Cache\SimpleCacheInterface;

class RedisCachedCursor implements CursorInterface
{
    public const SMALL_CACHE_SIZE = 1000;
    public const MEDIUM_CACHE_SIZE = 5000;
    public const LARGE_CACHE_SIZE = 10000;
    public const EXTRA_LARGE_CACHE_SIZE = 50000;

    /** @var int|mixed */
    private $position = 0;
    /** @var CursorInterface */
    private $cursor;
    /** @var SimpleCacheInterface */
    private $cache;
    /** @var string */
    private $identifier;
    /** @var string */
    private $buildId;
    /** @var array */
    private $items = [];
    /** @var array */
    private $options = [
        'cache_size' => self::MEDIUM_CACHE_SIZE,
        'ttl' => null,
    ];
    /** @var int|null */
    private $rangeStart = null;
    /** @var int|null */
    private $rangeEnd = null;
    /** @var array<string, bool> */
    private $cacheKeys = [];
    /** @var array<int, self> */
    private static $instances = [];

    public function __construct(CursorInterface $cursor, SimpleCacheInterface $cache, string $identifier, array $options = [], ?string $buildId = null)
    {
        $this->cursor = $cursor;
        $this->cache = $cache;
        $this->identifier = $identifier;
        $this->options = array_merge($this->options, $options);
        $this->buildId = $buildId ?? self::generateBuildId();
        $this->position = $cursor->key();
        self::$instances[spl_object_id($this)] = $this;
    }

    public static function create(CursorInterface $cursor, SimpleCacheInterface $cache, string $identifier, array $options = [], ?string $buildId = null): self
    {
        return new self($cursor, $cache, $identifier, $options, $buildId);
    }

    private static function generateBuildId(): string
    {
        return 'todo-' . bin2hex(random_bytes(8));
    }

    public function loop(callable $callable): void
    {
        foreach ($this->getIterator() as $row) {
            $callable($row);
        }
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
        $this->prefetch($this->position);

        return array_key_exists($this->position, $this->items) ? $this->items[$this->position] : false;
    }

    private function prefetch(int $i): void
    {
        if ($this->rangeStart !== null && $i >= $this->rangeStart && $i <= $this->rangeEnd) {
            return;
        }

        $cacheSize = (int) $this->options['cache_size'];
        if ($cacheSize < 1) {
            $cacheSize = 1;
        }

        $chunkIndex = intdiv($i, $cacheSize);
        $cacheKey = $this->getCacheKey($chunkIndex);
        if ($this->cache->has($cacheKey)) {
            $this->items = $this->cache->get($cacheKey, []);
            $this->rangeStart = $chunkIndex * $cacheSize;
            $this->rangeEnd = $this->rangeStart + $cacheSize - 1;
            $this->cacheKeys[$cacheKey] = true;
            return;
        }

        $this->rangeStart = $chunkIndex * $cacheSize;
        $this->rangeEnd = $this->rangeStart + $cacheSize - 1;
        $this->items = [];
        $collected = 0;

        while ($this->cursor->valid()) {
            $key = $this->cursor->key();
            $numericKey = is_int($key) ? $key : (is_string($key) && ctype_digit($key) ? (int) $key : null);
            if ($numericKey === null) {
                $this->cursor->next();
                continue;
            }

            if ($numericKey >= $this->rangeStart && $numericKey <= $this->rangeEnd) {
                $this->items[$numericKey] = $this->cursor->current();
                ++$collected;
                if ($collected >= $cacheSize) {
                    break;
                }
            }
            $this->cursor->next();
        }

        $this->cache->set($cacheKey, $this->items, $this->options['ttl']);
        $this->cacheKeys[$cacheKey] = true;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function valid(): bool
    {
        $this->prefetch($this->position);

        return array_key_exists($this->position, $this->items);
    }

    public function rewind(): void
    {
        $this->cursor->rewind();
        $this->position = $this->cursor->key();
    }

    public function seek($pointer): void
    {
        $this->position = (int) $pointer;
        if (!$this->valid()) {
            //throw new OutOfBoundsException('Invalid position');
        }
    }

    public function count(): int
    {
        return $this->cursor->count();
    }

    public function clear(): void
    {
        $this->clearCacheEntries();
        $this->items = [];
        $this->rangeStart = null;
        $this->rangeEnd = null;
        $this->rewind();
        $this->cursor->clear();
    }

    public function clearCacheEntries(): void
    {
        if ($this->cacheKeys !== []) {
            $this->cache->deleteMultiple(array_keys($this->cacheKeys));
            $this->cacheKeys = [];
        }
    }

    public static function clearRegisteredCaches(): void
    {
        foreach (self::$instances as $instance) {
            $instance->clearCacheEntries();
        }
        self::$instances = [];
    }

    private function getCacheKey(int $chunkIndex): string
    {
        $identifier = trim($this->identifier, '/');
        $buildId = trim($this->buildId, '/');

        return '/TRANSFORMATION/' . $buildId . '/' . $identifier . '/chunk:' . $chunkIndex;
    }
}
