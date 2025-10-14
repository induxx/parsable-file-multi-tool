<?php

namespace Misery\Component\Converter;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Common\Registry\Registry;
use Misery\Component\Decoder\ItemDecoder;
use Misery\Component\Decoder\ItemDecoderFactory;
use Misery\Component\Encoder\ItemEncoder;
use Misery\Component\Encoder\ItemEncoderFactory;
use Misery\Component\Modifier\ArrayFlattenModifier;
use Misery\Component\Modifier\ArrayUnflattenModifier;

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
class XmlExtractionV1Converter implements ConverterInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private ItemEncoder $encoder;
    private ItemDecoder $decoder;

    private array $options = [
        'separator' => '|',
        'extract'   => [],
        'parse' => [
            'flatten' => [
                'separator' => '|',
            ]
        ],
        'properties' => [],
    ];

    public function __construct()
    {
        $encoder = new ItemEncoderFactory();
        $decoder = new ItemDecoderFactory();
        $modifierRegistry = new Registry('modifier');
        $modifierRegistry
            ->register(ArrayUnflattenModifier::NAME, new ArrayUnflattenModifier())
            ->register(ArrayFlattenModifier::NAME, new ArrayFlattenModifier())
        ;

        $encoder->addRegistry($modifierRegistry);
        $decoder->addRegistry($modifierRegistry);
        $this->encoder = $encoder->createItemEncoder([
            'encode' => $this->getOption('properties'),
            'parse' => $this->getOption('parse'),
        ]);
        $this->decoder = $decoder->createItemDecoder([
            'decode' => $this->getOption('properties'),
            'parse' => $this->getOption('parse'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $item): array
    {
        $rules = $this->getOption('extract', []);
        $item = $this->applyExtractionRules($item, $rules);

        $item = $this->encoder->encode($item);

        return $item;
    }

    /** {@inheritdoc} */
    public function revert(array $item): array
    {
        $rules = $this->getOption('extract', []);
        $sep = $this->getOption('separator');
        $result = [];

        foreach ($item as $flatKey => $value) {
            $matched = false;
            foreach ($rules as $path => $rule) {
                $prefix = $path . $sep;
                if (strpos($flatKey, $prefix) !== 0) {
                    continue;
                }

                $matched = true;
                $subKey = substr($flatKey, strlen($prefix));

                // 1) Attribute-based extraction
                if (!empty($rule['k']) && str_starts_with($rule['k'], '@attributes.')) {
                    $parts = explode($sep, $subKey, 2);
                    $childTag = $parts[0];
                    $lang = $parts[1] ?? null;
                    $attrName = substr($rule['k'], strlen('@attributes.'));

                    $entry = ['@attributes' => [$attrName => $lang]];
                    $valuePath = $rule['v'] ?? '@value';
                    if ($valuePath === '@value') {
                        $entry['@value'] = $value;
                    } else {
                        $entry[$valuePath] = $value;
                    }

                    $result[$path . $sep . $childTag][] = $entry;
                }
                // 2) Standard key/value list extraction
                elseif (!empty($rule['k'])) {
                    $parts = explode($sep, $subKey, 2);
                    $entryKey = $parts[0];

                    $entry = [$rule['k'] => $entryKey];
                    if (isset($rule['v'])) {
                        $entry[$rule['v']] = $value;
                    }

                    $result[$path][] = $entry;
                }
                // 3) No rule ⇒ preserve flat key
                else {
                    $result[$flatKey] = $value;
                }

                break;
            }

            if (!$matched) {
                // Keys not covered by any extract rule
                $result[$flatKey] = $value;
            }
        }

        // Use decoder to unflatten the combined structure
        $decoded = $this->decoder->decode($result);

        // @todo Remove any leftover flat keys that contain the separator
        foreach ($decoded as $key => $val) {
            if (str_contains($key, $sep)) {
                unset($decoded[$key]);
            }
        }

        return $decoded;
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
                if (isset($value['@attributes']) || isset($value['@value'])) {
                    // Group @attributes and @value under the parent key
                    $flat[$fullKey] = $value;
                } elseif ($this->isAssoc($value)) {
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
    private function getNestedValue($data, array $keys): mixed
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
    public function getName(): string
    {
        return 'flatten/xml/v1';
    }
}
