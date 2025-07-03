<?php

namespace Misery\Component\Converter;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;

/**
 * Flattens and extracts nested XML-style arrays.
 *
 * Supports two modes of extraction:
 *
 * 1. **Key/Value lists** (e.g. PRODUCT_FEATURES):
 *    - specify 'k' and 'v' in your extract rule
 * 2. **Attribute-based lists** (e.g. multilingual PRODUCT_DETAILS):
 *    - specify 'k' => '@attributes.lang'
 *    - (optional) 'v' => '@value'  (defaults to '@value')
 *
 * Options:
 *   - separator: string delimiter for paths (default: '|')
 *   - extract:   map of path => extraction config
 */
class XmlExtractionConverter implements ConverterInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    protected array $options = [
        'separator' => '|',
        'extract'   => [],
    ];

    /**
     * {@inheritdoc}
     */
    public function convert(array $item): array
    {
        $rules = $this->getOption('extract', []);
        return $this->applyExtractionRules($item, $rules);
    }

    /**
     * Loop over each configured path and apply either:
     *  - attribute-based extraction, or
     *  - standard k/v list extraction, or
     *  - full flatten if no rule provided.
     *
     * @param  array<mixed> $data
     * @param  array<string, array{ k?: string, v?: string }> $rules
     * @return array<mixed>
     */
    private function applyExtractionRules(array $data, array $rules): array
    {
        $sep    = $this->getOption('separator');
        $output = [];

        foreach ($rules as $path => $rule) {
            $sub = $this->getNestedValue($data, explode($sep, $path));
            if ($sub === null) {
                continue;
            }

            // 1) Attribute-based mode: rule['k'] == '@attributes.xxx'
            if (!empty($rule['k']) && str_starts_with($rule['k'], '@attributes.')) {
                $attrName  = substr($rule['k'], strlen('@attributes.'));
                $valuePath = $rule['v'] ?? '@value';

                // Expect $sub to be assoc of child-tag => list of entries
                foreach ((array)$sub as $childTag => $entries) {
                    if (!is_array($entries)) {
                        continue;
                    }
                    foreach ($entries as $entry) {
                        if (
                            !is_array($entry)
                            || !isset($entry['@attributes'][$attrName])
                        ) {
                            continue;
                        }
                        $lang = $entry['@attributes'][$attrName];
                        $value = $this->getNestedValue(
                            $entry,
                            explode('.', ltrim($valuePath, '.'))
                        );
                        $output["{$path}{$sep}{$childTag}{$sep}{$lang}"] = $value;
                    }
                }
                continue;
            }

            // 2) Standard list extraction: needs both k and v
            if (!empty($rule['k'])) {
                foreach ((array)$sub as $entry) {
                    if (!is_array($entry) || !isset($entry[$rule['k']])) {
                        continue;
                    }
                    $key = (string)$entry[$rule['k']];
                    $value = $this->extractByKvRule($entry, $rule);
                    $output[$path][$key] = $value;
                }
                continue;
            }

            // 3) No rule ⇒ full subtree flatten
            $flattened = $this->flattenRecursive((array)$sub);
            $output[$path] = count($flattened) === 1
                ? reset($flattened)
                : $flattened;
        }

        return $output;
    }

    /**
     * For a single list entry and a ['k'=>…, 'v'=>…] rule,
     * return either the direct v-field, or flatten the rest.
     *
     * @param  array<mixed> $entry
     * @param  array{ k: string, v?: string } $rule
     * @return mixed
     */
    private function extractByKvRule(array $entry, array $rule)
    {
        if (isset($rule['v'], $entry[$rule['v']])) {
            return $entry[$rule['v']];
        }

        unset($entry[$rule['k']]);
        $flat = $this->flattenRecursive($entry);
        return count($flat) === 1 ? reset($flat) : $flat;
    }

    /**
     * Recursively flatten an associative array into
     * [ 'A|B|C' => scalar|array, … ].
     *
     * Numeric lists of scalars become full arrays.
     *
     * @param array<mixed>  $data
     * @param string        $prefix
     * @return array<string, mixed>
     */
    private function flattenRecursive(array $data, string $prefix = ''): array
    {
        $sep   = $this->getOption('separator');
        $flat  = [];

        foreach ($data as $key => $value) {
            $fullKey = $prefix === '' ? $key : "{$prefix}{$sep}{$key}";

            if (is_array($value)) {
                if ($this->isAssoc($value)) {
                    $flat += $this->flattenRecursive($value, $fullKey);
                } elseif ($this->isListOfScalars($value)) {
                    $flat[$fullKey] = $value;
                }
                // nested lists of arrays are dropped
            } else {
                $flat[$fullKey] = $value;
            }
        }

        return $flat;
    }

    /**
     * Safe nested lookup via a sequence of keys or attribute-paths.
     *
     * @param  mixed        $data
     * @param  string[]     $keys
     * @return mixed|null
     */
    private function getNestedValue($data, array $keys)
    {
        foreach ($keys as $k) {
            if (
                !is_array($data)
                || !array_key_exists($k, $data)
            ) {
                return null;
            }
            $data = $data[$k];
        }
        return $data;
    }

    /** Check if an array has string keys anywhere. */
    private function isAssoc(array $arr): bool
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /** Check if every element is non-array (a list of scalars). */
    private function isListOfScalars(array $arr): bool
    {
        foreach ($arr as $i) {
            if (is_array($i)) {
                return false;
            }
        }
        return true;
    }

    /** {@inheritdoc} */
    public function revert(array $item): array
    {
        return $item;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'flatten/xml';
    }
}
