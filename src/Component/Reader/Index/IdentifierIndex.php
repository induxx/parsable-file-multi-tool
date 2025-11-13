<?php

namespace Misery\Component\Reader\Index;

use Misery\Component\Reader\Index\Storage\IdentifierIndexStorageInterface;

final class IdentifierIndex
{
    public const ENTRY_ITEM = 'item';
    public const ENTRY_POSITION = 'position';

    private const DEFAULT_OPTIONS = [
        'allow_multiple_matches' => true,
        'sort_before_index' => true,
        'store_positions' => true,
        'normalize_identifier' => null,
    ];

    private string $field;
    private IdentifierIndexStorageInterface $storage;
    private bool $primed = false;
    private bool $allowMultipleMatches;
    private bool $sortBeforeIndex;
    private bool $storePositions;
    /** @var callable|null */
    private $normalizer;
    private string $backendLabel;

    public function __construct(string $field, IdentifierIndexStorageInterface $storage, array $options = [], ?string $backendLabel = null)
    {
        if ('' === trim($field)) {
            throw new \InvalidArgumentException('Identifier field cannot be empty.');
        }

        $options = array_merge(self::DEFAULT_OPTIONS, $options);
        if (null !== $options['normalize_identifier'] && !is_callable($options['normalize_identifier'])) {
            throw new \InvalidArgumentException('normalize_identifier must be a callable or null.');
        }

        $this->field = $field;
        $this->storage = $storage;
        $this->allowMultipleMatches = (bool) $options['allow_multiple_matches'];
        $this->sortBeforeIndex = (bool) $options['sort_before_index'];
        $this->storePositions = (bool) $options['store_positions'];
        $this->normalizer = $options['normalize_identifier'];
        $this->backendLabel = $backendLabel ?? $this->detectBackendLabel($storage);
    }

    public function getBackendLabel(): string
    {
        return $this->backendLabel;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function isPrimed(): bool
    {
        return $this->primed;
    }

    /**
     * @param iterable<int|string, array<string, mixed>> $rows
     */
    public function prime(iterable $rows): void
    {
        if ($this->primed) {
            return;
        }

        $bucket = [];

        foreach ($rows as $position => $row) {
            if (!\is_array($row) || !array_key_exists($this->field, $row)) {
                continue;
            }

            $identifier = $row[$this->field];
            if ($identifier === null || $identifier === '') {
                continue;
            }

            $normalized = $this->normalizeIdentifier((string) $identifier);

            if (!$this->allowMultipleMatches && isset($bucket[$normalized])) {
                continue;
            }

            $entry = [
                self::ENTRY_ITEM => $row,
            ];

            if ($this->storePositions) {
                $entry[self::ENTRY_POSITION] = $position;
            }

            $bucket[$normalized][] = $entry;
        }

        if ($bucket === []) {
            $this->primed = true;
            return;
        }

        if ($this->sortBeforeIndex) {
            uksort($bucket, static fn (string $left, string $right): int => strnatcmp($left, $right));
        }

        $this->storage->hydrate($bucket);
        $this->primed = true;
    }

    /**
     * @param string|int $identifier
     *
     * @return array<int, array<string, mixed>>
     */
    public function match(string|int $identifier): array
    {
        $hit = $this->storage->fetch($this->normalizeIdentifier((string) $identifier));

        return $this->flatten($hit ?? []);
    }

    /**
     * @param string[] $identifiers
     *
     * @return array<int, array<string, mixed>>
     */
    public function matchMany(array $identifiers): array
    {
        if ($identifiers === []) {
            return [];
        }

        $normalized = array_values(array_unique(array_map(fn ($value): string => $this->normalizeIdentifier((string) $value), $identifiers)));
        $hits = $this->storage->fetchMany($normalized);

        $rows = [];
        foreach ($hits as $entries) {
            $rows = array_merge($rows, $this->flatten($entries));
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allRows(): array
    {
        $rows = [];
        foreach ($this->storage->all() as $entries) {
            $rows = array_merge($rows, $this->flatten($entries));
        }

        return $rows;
    }

    public function clear(): void
    {
        $this->storage->clear();
        $this->primed = false;
    }

    private function normalizeIdentifier(string $identifier): string
    {
        if (null === $this->normalizer) {
            return $identifier;
        }

        return (string) ($this->normalizer)($identifier);
    }

    /**
     * @param array<int, array<string, mixed>> $entries
     *
     * @return array<int, array<string, mixed>>
     */
    private function flatten(array $entries): array
    {
        $rows = [];
        foreach ($entries as $entry) {
            if (!isset($entry[self::ENTRY_ITEM]) || !\is_array($entry[self::ENTRY_ITEM])) {
                continue;
            }
            $rows[] = $entry[self::ENTRY_ITEM];
        }

        return $rows;
    }

    private function detectBackendLabel(IdentifierIndexStorageInterface $storage): string
    {
        return $storage instanceof \Misery\Component\Reader\Index\Storage\RedisIdentifierIndexStorage
            ? 'Redis Reader|Writer'
            : 'Memory Reader|Writer';
    }
}
