<?php

namespace Misery\Component\Converter;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;

class XmlExtractionConverter implements ConverterInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private $options = [
        'separator' => '|',
        'extract' => [],
    ];

    public function convert(array $item): array
    {
        return $this->flatten($item, $this->getOption('extract'));
    }

    public function flatten(array $array, array $extractList): array
    {
        $separator = $this->getOption('separator');
        $result = [];

        foreach ($extractList as $path => $extractKey) {
            $segments = explode($separator, $path);
            $sub = self::getNested($array, $segments);
            if ($sub === null) {
                continue;
            }

            // If no extractKey: flatten the sub-array itself
            if (empty($extractKey)) {
                $flattened = self::flattenRecursive(
                    (array) $sub,
                    $separator,
                    ''
                );
                $value = count($flattened) === 1
                    ? reset($flattened)
                    : $flattened;

                $result[$path] = $value;
                continue;
            }

            // Extract by key: expect $sub to be a list of items
            foreach ((array) $sub as $item) {
                if (!is_array($item) || !isset($item[$extractKey['k']])) {
                    continue;
                }
                $key = $item[$extractKey['k']];
                $value = null;

                if (isset($item[$extractKey['v']])) {
                    $value = $item[$extractKey['v']];
                } else {
                    // Otherwise flatten remaining item values
                    $tmp = $item;
                    unset($tmp[$extractKey['k']]);
                    $flatItem = self::flattenRecursive($tmp, $separator, '');
                    $value = count($flatItem) === 1
                        ? reset($flatItem)
                        : $flatItem;
                }

                $result[$path][$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Recursively flatten an associative array to dot-notation keys.
     * Joins numeric-value-only arrays with comma.
     */
    private static function flattenRecursive(array $array, string $sep, string $prefix): array
    {
        $out = [];
        foreach ($array as $k => $v) {
            $currentKey = $prefix !== '' ? $prefix . $sep . $k : $k;

            if (is_array($v)) {
                if (self::isAssoc($v)) {
                    $out += self::flattenRecursive($v, $sep, $currentKey);
                } else {
                    // Numeric array of scalars
                    if (array_reduce($v, fn($c, $i) => $c && !is_array($i), true)) {
                        $out[$currentKey] = $v;
                    }
                }
            } else {
                $out[$currentKey] = $v;
            }
        }
        return $out;
    }

    /**
     * Retrieve a nested value by path segments.
     */
    private static function getNested(array $array, array $segments)
    {
        $current = $array;
        foreach ($segments as $seg) {
            if (!is_array($current) || !array_key_exists($seg, $current)) {
                return null;
            }
            $current = $current[$seg];
        }
        return $current;
    }

    /**
     * Check if array is associative.
     */
    private static function isAssoc(array $arr): bool
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public function revert(array $item): array
    {
        return $item;
    }

    public function getName(): string
    {
        return 'flatten/xml';
    }
}