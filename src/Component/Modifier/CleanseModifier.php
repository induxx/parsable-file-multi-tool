<?php

namespace Misery\Component\Modifier;

use Misery\Component\Common\Modifier\RowModifier;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class CleanseModifier implements RowModifier, OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'cleanse';

    /**
     * Default options:
     * - remove_any: characters or substrings to remove anywhere in the value
     * - remove_start: substrings to strip from the start (repeatedly)
     * - remove_end: substrings to strip from the end (repeatedly)
     */
    private array $options = [
        'remove_any'   => [],
        'remove_start' => [],
        'remove_end'   => [],
    ];

    /**
     * Apply cleansing to a value or array of values.
     * @param mixed $value
     * @return mixed
     */
    public function modify($value)
    {
        $removeAny   = $this->options['remove_any'] ?? [];
        $removeStart = $this->options['remove_start'] ?? [];
        $removeEnd   = $this->options['remove_end'] ?? [];

        // Handle arrays recursively
        if (is_array($value)) {
            return array_map([$this, 'modify'], $value);
        }

        // Only process strings
        if (!is_string($value)) {
            return $value;
        }

        // 1. Remove any specified characters/substrings anywhere
        if (!empty($removeAny)) {
            $value = str_replace($removeAny, '', $value);
        }

        // 2. Strip specified prefixes repeatedly
        foreach ($removeStart as $prefix) {
            $length = strlen($prefix);
            while ($length > 0 && substr($value, 0, $length) === $prefix) {
                $value = substr($value, $length);
            }
        }

        // 3. Strip specified suffixes repeatedly
        foreach ($removeEnd as $suffix) {
            $length = strlen($suffix);
            while ($length > 0 && substr($value, -$length) === $suffix) {
                $value = substr($value, 0, -$length);
            }
        }

        return $value;
    }

    /**
     * Reverse cleansing (no-op in this modifier).
     * @param mixed $value
     * @return mixed
     */
    public function reverseModify($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'reverseModify'], $value);
        }

        // No reverse operation; return value unchanged
        return $value;
    }
}
