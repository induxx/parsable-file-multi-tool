<?php

namespace Misery\Component\Common\Cursor;

/**
 * This class make a in memory table of your reference + zoneNR + file-index
 */
class ZoneFileIndexer
{
    /**
     * Adapt the cache size to fit your memory requirements
     * for large column items, it's best to reduce the cache to a smaller size
     *
     * Smaller cache_size equals more rotation, lowers the performance
     */
    public const SMALL_CACHE_SIZE = 1000;
    public const MEDIUM_CACHE_SIZE = 10000;
    private const INDEX_OFFSET = 1;
    private const ZONE_CALCULATION_DIVISOR = 2;

    private array $indexes = [];
    private array $zones = [];
    private int $cacheSize;

    public function __construct(?int $cacheSize)
    {
        $this->cacheSize = $cacheSize ?? self::MEDIUM_CACHE_SIZE;
    }

    public function init(CursorInterface $cursor, string $reference): void
    {
        if ($this->indexes === []) {
            // Prep indexes
            $cursor->loop(function ($row) use ($cursor, $reference) {
                if ($row) {
                    $index = (int) $cursor->key(); # line number
                    $zone = (int) (($index -self::INDEX_OFFSET) / $this->cacheSize); # grouping number
                    $referenceValue = $row[$reference] ?? null;
                    if ($referenceValue) {
                        $this->indexes[crc32($referenceValue)] = $index;
                        $this->zones[$index] = $zone;
                    }
                }
            });
            $cursor->rewind();
        }
    }

    public function removeFileIndex(string $reference, int $index): void
    {
        unset($this->zones[$index]);
        unset($this->indexes[crc32($reference)]);
    }

    public function getFileIndexByReference(string $reference): ?int
    {
        return $this->indexes[crc32($reference)] ?? null;
    }

    public function getZoneByFileIndex(int $index): ?int
    {
        return $this->zones[$index] ?? null;
    }

    public function getRangeFromZone(int $zone): array
    {
        $keys = [];
        foreach ($this->zones as $key => $linkedZone) {
            if ($linkedZone === $zone) {
                $keys[] = $key;
            }
        }

        return $keys;
    }
}
