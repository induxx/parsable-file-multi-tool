<?php

namespace Misery\Component\Csv\Reader;

use Misery\Component\Common\Cursor\CursorInterface;
use Misery\Component\Common\Processor\CsvDataProcessor;
use Misery\Component\Common\Processor\NullDataProcessor;
use Misery\Component\Csv\Cache\CacheCollector;

class CsvReader implements ReaderInterface
{
    private $cursor;
    private $cache;

    public function __construct(CursorInterface $cursor)
    {
        $this->cursor = $cursor;
        $this->cache = new CacheCollector();
    }

    public function getCursor(): CursorInterface
    {
        return $this->cursor;
    }

    public function loop(callable $callable): void
    {
        while ($this->cursor->valid()) {
            $callable($this->cursor->current());
            $this->cursor->next();
        }

        $this->cursor->rewind();
    }

    public function getRow(int $line): array
    {
        return current($this->getRows([$line])) ?? [];
    }

    public function getColumn(string $columnName): array
    {
        if (false === $this->cache->hasKey($columnName)) {
            $columnValues = [];
            $this->loop(function ($row) use (&$columnValues, $columnName) {
                $columnValues[$this->cursor->key()] = $row[$columnName];
            });

            $this->cache->setCache($columnName, $columnValues);

            return $columnValues;
        }

        return $this->cache->getCache($columnName) ?? [];
    }

    public function indexColumns(string ...$columnNames): void
    {
        foreach ($columnNames as $columnName) {
            $this->indexColumn($columnName);
        }
    }

    public function indexColumn(string $columnName): void
    {
        $this->cache->setCache($columnName, $this->getColumn($columnName));
    }

    public function getRows(array $lines): array
    {
        $collect = [];
        foreach ($lines as $lineNr) {
            $this->cursor->seek($lineNr);
            $collect[$lineNr] = $this->cursor->current();
        }

        $this->cursor->rewind();

        return $collect;
    }

    private function filter(callable $callable): array
    {
        $values = [];
        $this->loop(function ($row) use (&$values, $callable) {
            if (true === $callable($row)) {
                $values[$this->cursor->key()] = $row;
            }
        });

        return $values;
    }

    public function findOneBy(array $filter): array
    {
        return current($this->findBy($filter)) ?: [];
    }

    public function findBy(array $filter): array
    {
        $cursor = $this->cursor;
        foreach ($filter as $key => $value) {
            $this->cursor = new ItemCollection($this->processFilter($key, $value));
        }
        // rotate back the cursor
        $rows = $this->cursor;
        $this->cursor = $cursor;

        // fetch the values for these line numbers
        return $rows->getValues();
    }

    private function processFilter($key, $value): array
    {
        if ($this->cache->hasKey($key)) {
            // cached lineNr to get rows per lineNr
            $rows = $this->getRows(array_keys($this->cache->filterCache($key, static function ($row) use ($value) {
                return $value === $row;
            })));
        } else {
            $rows = $this->filter(static function ($row) use ($value, $key) {
                return $row[$key] === $value;
            });
        }

        return $rows;
    }
}
