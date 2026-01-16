<?php

namespace Misery\Component\Reader;

use Misery\Component\Common\Cursor\CursorInterface;
use Misery\Component\Common\Cursor\ItemCursor;

class ItemReader implements ItemReaderInterface
{
    private $cursor;
    /** @var array<string, array<string, array<int|string, array>>> */
    private $indices = [];
    /** @var array<int|string, int> */
    private $indexOrder = [];

    public function __construct(\Iterator $cursor)
    {
        $this->cursor = $cursor;
    }

    /** @inheritDoc */
    public function read()
    {
        $item = $this->cursor->current();
        if ($item === false) {
            return false;
        }

        $this->cursor->next();

        return $item;
    }

    public function index(array $lines): ItemReaderInterface
    {
        return new self($this->processIndex($lines));
    }

    private function processIndex(array $lines): \Generator
    {
        foreach ($lines as $lineNr) {
            $this->cursor instanceof \SeekableIterator ? $this->cursor->seek($lineNr) : $this->seek($lineNr);
            yield $lineNr => $this->cursor->current();
        }
    }

    /**
     * Adds seek support for \Iterator objects
     */
    public function seek($pointer): void
    {
        $this->cursor->rewind();
        while ($this->cursor->valid()) {
            if ($this->cursor->key() === $pointer) {
                break;
            }
            $this->cursor->next();
        }

        // @TODO throw outofboundexception
    }

    public function filterByList(array $constraint): ReaderInterface
    {
        $reader = $this;
        $columnName = $constraint['field'];
        $list = $constraint['list'];

        $reader = $reader->filter(static function ($row) use ($columnName, $list) {
            return in_array($row[$columnName], $list);
        });

        return $reader;
    }

    public function find(array $constraints): ReaderInterface
    {
        $prepared = [];
        foreach ($constraints as $columnName => $rowValue) {
            if (is_array($rowValue)) {
                $prepared[] = [
                    'type' => 'IN_LIST',
                    'column' => $columnName,
                    'list' => $rowValue,
                ];
                continue;
            }

            if (is_string($rowValue) && in_array($rowValue, ['UNIQUE', 'IS_NOT_NUMERIC', 'NOT_EMPTY', 'NOT_NULL'], true)) {
                $prepared[] = [
                    'type' => $rowValue,
                    'column' => $columnName,
                ];
                continue;
            }

            $prepared[] = [
                'type' => 'EQUALS',
                'column' => $columnName,
                'value' => $rowValue,
            ];
        }

        $indexedResult = $this->tryIndexedFind($prepared);
        if (null !== $indexedResult) {
            return $indexedResult;
        }

        $uniqueSet = [];
        $uniqueList = [];

        return $this->filter(static function ($row) use ($prepared, &$uniqueSet, &$uniqueList) {
            foreach ($prepared as $constraint) {
                $columnName = $constraint['column'];
                switch ($constraint['type']) {
                    case 'IN_LIST':
                        if (!in_array($row[$columnName], $constraint['list'])) {
                            return false;
                        }
                        break;
                    case 'UNIQUE':
                        if (!is_array($row)) {
                            return false;
                        }

                        $id = $row[$columnName];
                        if (is_int($id)) {
                            $key = 'i:' . $id;
                            if (array_key_exists($key, $uniqueSet)) {
                                return false;
                            }
                            $uniqueSet[$key] = true;
                        } elseif (is_string($id)) {
                            $key = 's:' . $id;
                            if (array_key_exists($key, $uniqueSet)) {
                                return false;
                            }
                            $uniqueSet[$key] = true;
                        } else {
                            if (in_array($id, $uniqueList, true)) {
                                return false;
                            }
                            $uniqueList[] = $id;
                        }
                        break;
                    case 'IS_NOT_NUMERIC':
                        if (is_numeric($row[$columnName])) {
                            return false;
                        }
                        break;
                    case 'NOT_EMPTY':
                        if (empty($row[$columnName])) {
                            return false;
                        }
                        break;
                    case 'NOT_NULL':
                        if ($row[$columnName] === null) {
                            return false;
                        }
                        break;
                    case 'EQUALS':
                        if (!($row && array_key_exists($columnName, $row) && $row[$columnName] === $constraint['value'])) {
                            return false;
                        }
                        break;
                }
            }

            return true;
        });
    }

    /**
     * @param array<int, array<string, mixed>> $constraints
     */
    private function tryIndexedFind(array $constraints): ?ReaderInterface
    {
        if ($constraints === []) {
            return null;
        }

        foreach ($constraints as $constraint) {
            if (!in_array($constraint['type'], ['IN_LIST', 'EQUALS'], true)) {
                return null;
            }
        }

        $columns = [];
        foreach ($constraints as $constraint) {
            $columns[] = $constraint['column'];
        }
        $this->ensureIndices($columns);

        $result = null;
        foreach ($constraints as $constraint) {
            $column = $constraint['column'];
            if (!isset($this->indices[$column])) {
                return null;
            }

            if ($constraint['type'] === 'EQUALS') {
                $valueKey = $this->getIndexKey($constraint['value']);
                if ($valueKey === null) {
                    return null;
                }
                $matches = $this->indices[$column][$valueKey] ?? [];
            } else {
                $matches = [];
                foreach ($constraint['list'] as $value) {
                    $valueKey = $this->getIndexKey($value);
                    if ($valueKey === null) {
                        return null;
                    }
                    if (!isset($this->indices[$column][$valueKey])) {
                        continue;
                    }
                    foreach ($this->indices[$column][$valueKey] as $itemKey => $row) {
                        if (!array_key_exists($itemKey, $matches)) {
                            $matches[$itemKey] = $row;
                        }
                    }
                }
            }

            if ($result === null) {
                $result = $matches;
                continue;
            }

            $result = array_intersect_key($result, $matches);
            if ($result === []) {
                break;
            }
        }

        if ($result === null) {
            return null;
        }

        if ($result !== [] && $this->indexOrder !== []) {
            $indexOrder = $this->indexOrder;
            uksort($result, static function ($a, $b) use ($indexOrder): int {
                return ($indexOrder[$a] ?? 0) <=> ($indexOrder[$b] ?? 0);
            });
        }

        return new self(new ItemCollection($result));
    }

    /**
     * @param array<int, string> $columns
     */
    private function ensureIndices(array $columns): void
    {
        $missing = array_diff($columns, array_keys($this->indices));
        if ($missing === []) {
            return;
        }

        $position = count($this->indexOrder);
        foreach ($this->getIterator() as $key => $row) {
            if (!is_array($row)) {
                continue;
            }
            if (!array_key_exists($key, $this->indexOrder)) {
                $this->indexOrder[$key] = $position;
                ++$position;
            }

            foreach ($missing as $column) {
                if (!array_key_exists($column, $row)) {
                    continue;
                }
                $indexKey = $this->getIndexKey($row[$column]);
                if ($indexKey === null) {
                    continue;
                }
                $this->indices[$column][$indexKey][$key] = $row;
            }
        }

        $this->cursor->rewind();
    }

    private function getIndexKey($value): ?string
    {
        if ($value === null) {
            return 'n:null';
        }
        if (is_int($value)) {
            return 'i:' . $value;
        }
        if (is_string($value)) {
            return 's:' . $value;
        }
        if (is_bool($value)) {
            return 'b:' . ($value ? '1' : '0');
        }
        if (is_float($value)) {
            return 'f:' . rtrim(rtrim(sprintf('%.14F', $value), '0'), '.');
        }

        return null;
    }

    public function filter(callable $callable): ReaderInterface
    {
        return new self($this->processFilter($callable));
    }

    private function processFilter(callable $callable): \Generator
    {
        foreach ($this->getIterator() as $key => $row) {
            if (true === $callable($row)) {
                yield $key => $row;
            }
        }
    }

    public function map(callable $callable): ReaderInterface
    {
        return new self($this->processMap($callable));
    }

    private function processMap(callable $callable): \Generator
    {
        foreach ($this->getIterator() as $key => $row) {
            if (is_array($row)) {
                yield $key => $callable($row);
            }
        }
    }

    public function getCursor(): CursorInterface
    {
        return new ItemCursor($this);
    }

    public function getIterator(): \Iterator
    {
        return $this->cursor;
    }

    public function clear(): void
    {
        if ($this->cursor instanceof CursorInterface) {
            $this->cursor->clear();
        }
    }

    public function getItems(): array
    {
        $items = iterator_to_array($this->cursor);

        return array_filter($items, static function ($item) {
            return is_array($item);
        });
    }
}
