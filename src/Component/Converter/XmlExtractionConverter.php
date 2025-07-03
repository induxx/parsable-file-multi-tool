<?php

namespace Misery\Component\Converter;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;

/**
 * Flattens and extracts nested XML-like arrays into a simple key/value structure.
 *
 * Options:
 *   - separator (string): delimiter for nested path segments (default: '|')
 *   - extract  (array):  map of path => extraction rules
 */
class XmlExtractionConverter implements ConverterInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    /** @var array{separator:string, extract:array<string, mixed>} */
    protected array $options = [
        'separator' => '|',
        'extract'   => [],
    ];

    /**
     * Convert an input item by flattening according to configured extract rules.
     *
     * @param array<mixed> $item
     * @return array<mixed>
     */
    public function convert(array $item): array
    {
        $paths = $this->getOption('extract', []);
        return $this->applyExtractionRules($item, $paths);
    }

    /**
     * @param array<mixed> $data
     * @param array<string, array{ k?: string, v?: string }> $rules
     * @return array<mixed>
     */
    private function applyExtractionRules(array $data, array $rules): array
    {
        $separator = $this->getOption('separator');
        $output = [];

        foreach ($rules as $path => $rule) {
            $segments = explode($separator, $path);
            $segmentValue = $this->getNestedValue($data, $segments);

            if ($segmentValue === null) {
                continue;
            }

            // No extraction key: flatten this subtree entirely
            if (empty($rule['k']) && empty($rule['v'])) {
                $output[$path] = $this->flattenSubtree($segmentValue);
                continue;
            }

            // Extract list items by key/value
            foreach ((array) $segmentValue as $entry) {
                if (!is_array($entry) || !isset($entry[$rule['k']])) {
                    continue;
                }

                $key = (string) $entry[$rule['k']];
                $value = $this->extractValueFromEntry($entry, $rule);
                $output[$path][$key] = $value;
            }
        }

        return $output;
    }

    /**
     * Flatten any array or scalar into a simple value or list.
     *
     * @param mixed $value
     * @return mixed
     */
    private function flattenSubtree($value)
    {
        $array = (array) $value;
        $flat   = $this->flattenRecursive($array);

        // If single scalar, return it directly
        if (count($flat) === 1) {
            return reset($flat);
        }

        return $flat;
    }

    /**
     * Extract the "value" from a single list entry, either by its 'v' key
     * or by flattening the remainder of the array.
     *
     * @param array<mixed> $entry
     * @param array{ k: string, v?: string } $rule
     * @return mixed
     */
    private function extractValueFromEntry(array $entry, array $rule)
    {
        // Direct mapping
        if (isset($rule['v'], $entry[$rule['v']])) {
            return $entry[$rule['v']];
        }

        // Otherwise, flatten all other fields
        unset($entry[$rule['k']]);
        $flat = $this->flattenRecursive($entry);

        return count($flat) === 1
            ? reset($flat)
            : $flat;
    }

    /**
     * Recursively flatten a nested associative array into a flat map of
     * "path" => value, where numeric-only lists become arrays.
     *
     * @param array<mixed> $data
     * @param string $prefix  current path prefix
     * @return array<string, mixed>
     */
    private function flattenRecursive(array $data, string $prefix = ''): array
    {
        $sep   = $this->getOption('separator');
        $flat  = [];

        foreach ($data as $key => $value) {
            $fullKey = $prefix === '' ? $key : $prefix . $sep . $key;

            if (is_array($value)) {
                if ($this->isAssoc($value)) {
                    $flat += $this->flattenRecursive($value, $fullKey);
                } elseif ($this->isListOfScalars($value)) {
                    $flat[$fullKey] = $value;
                }
                // ignore nested lists of arrays at this level
            } else {
                $flat[$fullKey] = $value;
            }
        }

        return $flat;
    }

    /**
     * Safely retrieve a nested value from an array by a list of keys.
     *
     * @param array<mixed> $data
     * @param array<string> $keys
     * @return mixed|null
     */
    private function getNestedValue(array $data, array $keys)
    {
        foreach ($keys as $key) {
            if (!is_array($data) || !array_key_exists($key, $data)) {
                return null;
            }
            $data = $data[$key];
        }
        return $data;
    }

    /**
     * Determine if an array is associative.
     */
    private function isAssoc(array $arr): bool
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Check if an array is a flat list of scalar values.
     *
     * @param array<mixed> $arr
     */
    private function isListOfScalars(array $arr): bool
    {
        foreach ($arr as $item) {
            if (is_array($item)) {
                return false;
            }
        }
        return true;
    }

    /** {@inheritdoc} */
    public function revert(array $item): array
    {
        // No-op for XML extraction
        return $item;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'flatten/xml';
    }
}
