<?php

namespace Misery\Component\Reader;

use Misery\Component\Common\Cursor\CursorInterface;
use Misery\Component\Common\Cursor\ItemCursor;
use Misery\Component\Reader\Index\IdentifierIndex;
use Misery\Component\Reader\Index\Storage\ArrayIdentifierIndexStorage;
use Misery\Component\Reader\Index\Storage\IdentifierIndexStorageInterface;

class ItemReader implements ItemReaderInterface
{
    private $cursor;
    private ?IdentifierIndex $identifierIndex = null;
    private ?string $identifierBackendLabel = null;

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

    /**
     * Materialize the current reader and build an identifier index. The
     * original iterator is consumed, so make sure to use the returned reader
     * afterwards.
     */
    public function withIdentifierIndex(
        string $identifierField,
        ?IdentifierIndexStorageInterface $storage = null,
        array $options = []
    ): ItemReaderInterface {
        $options = array_merge(['sort_before_index' => false], $options);
        $rows = $this->collectRowsForIndexing();
        $storage = $storage ?? new ArrayIdentifierIndexStorage();
        $index = new IdentifierIndex($identifierField, $storage, $options);
        $index->prime($rows);

        if ($options['sort_before_index'] === true) {
            $rows = $index->allRows();
        }

        $reader = new self(new ItemCollection($rows));
        $reader->identifierIndex = $index;
        $reader->identifierBackendLabel = $index->getBackendLabel();

        return $reader;
    }

    public function sortOnIdentifier(
        string $identifierField,
        ?IdentifierIndexStorageInterface $storage = null,
        array $options = []
    ): ItemReaderInterface {
        $options = array_merge(['sort_before_index' => true], $options);
        return $this->withIdentifierIndex($identifierField, $storage, $options);
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
        if ($optimized = $this->tryIdentifierShortcut($constraints)) {
            return $optimized;
        }

        $reader = $this;
        foreach ($constraints as $columnName => $rowValue) {
            if (is_array($rowValue)) {
                $reader = $reader->filter(static function ($row) use ($rowValue, $columnName) {
                    return in_array($row[$columnName], $rowValue);
                });
            } elseif (is_string($rowValue) && in_array($rowValue, ['UNIQUE', 'IS_NOT_NUMERIC', 'NOT_EMPTY', 'NOT_NULL'])) {
                if ($rowValue === 'UNIQUE') {
                    $list = [];
                    $reader = $reader->filter(static function ($row) use ($columnName, &$list) {
                        if (!is_array($row)) {
                            return false;
                        }

                        $id = $row[$columnName];
                        if (in_array($id, $list, true)) {
                            return false;
                        }
                        $list[] = $id;
                        return true;
                    });
                } elseif ($rowValue === 'IS_NOT_NUMERIC') {
                    $reader = $reader->filter(static function ($row) use ($columnName) {
                        return !is_numeric($row[$columnName]);
                    });
                } elseif ($rowValue === 'NOT_EMPTY') {
                    $reader = $reader->filter(static function ($row) use ($columnName) {
                        return !empty($row[$columnName]);
                    });
                } elseif ($rowValue === 'NOT_NULL') {
                    $reader = $reader->filter(static function ($row) use ($columnName) {
                        return false === ($row[$columnName] === NULL);
                    });
                }
            } else {
                $reader = $reader->filter(static function ($row) use ($rowValue, $columnName) {
                    return $row && array_key_exists($columnName, $row) && $row[$columnName] === $rowValue;
                });
            }
        }

        return $reader;
    }

    private function tryIdentifierShortcut(array $constraints): ?ReaderInterface
    {
        if (null === $this->identifierIndex) {
            return null;
        }

        $identifierField = $this->identifierIndex->getField();
        if (!array_key_exists($identifierField, $constraints)) {
            return null;
        }

        $value = $constraints[$identifierField];
        if ($this->isKeywordConstraint($value)) {
            return null;
        }
        $values = $this->normalizeConstraintValues($value);
        if ($values === null) {
            return null;
        }

        $rows = $this->identifierIndex->matchMany($values);
        unset($constraints[$identifierField]);

        if ($rows === []) {
            return $this->createReaderFromRows([]);
        }

        $reader = $this->createReaderFromRows($rows, $this->identifierIndex);
        if ([] === $constraints) {
            return $reader;
        }

        if ($reader instanceof self) {
            $reader->identifierIndex = null;
            $reader->identifierBackendLabel = null;
        }

        return $reader->find($constraints);
    }

    private function isKeywordConstraint($value): bool
    {
        return is_string($value) && in_array($value, ['UNIQUE', 'IS_NOT_NUMERIC', 'NOT_EMPTY', 'NOT_NULL'], true);
    }

    private function normalizeConstraintValues($value): ?array
    {
        if (is_array($value)) {
            $values = array_values(array_filter($value, static fn ($val): bool => is_scalar($val) || (is_object($val) && method_exists($val, '__toString'))));
            return array_map(static fn ($val): string => (string) $val, $values);
        }

        if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
            return [(string) $value];
        }

        return null;
    }

    private function createReaderFromRows(array $rows, ?IdentifierIndex $index = null): ReaderInterface
    {
        $reader = new self(new \ArrayIterator($rows));

        if (null !== $index && $reader instanceof self) {
            $reader->identifierIndex = $index;
            $reader->identifierBackendLabel = $index->getBackendLabel();
        }

        return $reader;
    }

    private function collectRowsForIndexing(): array
    {
        if (!$this->cursor instanceof \Iterator) {
            return [];
        }

        try {
            $this->cursor->rewind();
        } catch (\Throwable $exception) {
            throw new \RuntimeException('Unable to rewind cursor before building the identifier index. Consider wrapping the cursor with a CachedCursor.', 0, $exception);
        }

        $rows = [];
        while ($this->cursor->valid()) {
            $current = $this->cursor->current();
            $rows[$this->cursor->key()] = $current;
            $this->cursor->next();
        }

        try {
            $this->cursor->rewind();
        } catch (\Throwable $exception) {
            // Generators cannot rewind after being consumed; converting to a
            // materialized reader is expected in that scenario so we simply
            // swallow the exception.
        }

        return $rows;
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

        if (null !== $this->identifierIndex) {
            $this->identifierIndex->clear();
        }

        $this->identifierBackendLabel = null;
    }

    public function getItems(): array
    {
        return iterator_to_array($this->cursor);
    }

    public function getIdentifierBackendLabel(): string
    {
        if (null !== $this->identifierIndex) {
            return $this->identifierIndex->getBackendLabel();
        }

        return $this->identifierBackendLabel ?? 'Memory Reader|Writer';
    }
}
