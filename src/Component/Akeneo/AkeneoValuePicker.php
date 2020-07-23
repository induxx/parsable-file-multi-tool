<?php

namespace Misery\Component\Akeneo;

class AkeneoValuePicker
{
    private static $default = [
        'locale' => null,
        'scope' => null,
    ];

    public static function pick(array $item, string $key, array $context = [])
    {
        $context = array_merge(self::$default, $context);

        $itemValue = $item[$key] ?? null;

        if ($itemValue) {
            // akeneo value picker
            if (null === $context['scope'] && null === $context['locale']) {
                return $itemValue;
            }
            if ($context['scope'] && $context['locale'] && isset($itemValue[$context['scope']][$context['locale']])) {
                return $itemValue[$context['scope']][$context['locale']];
            }
            elseif ($context['scope'] && isset($itemValue[$context['scope']])) {
                return $itemValue[$context['scope']];
            }
            elseif ($context['locale'] && isset($itemValue[$context['locale']])) {
                return $itemValue[$context['locale']];
            }
        }

        return $itemValue;
    }

    public static function pickLabel(array $item, array $context = [])
    {
        $context = array_merge(self::$default, $context);

        $itemValue = $item['label'] ?? null;

        if ($itemValue) {
            // akeneo value picker
            if (null === $context['scope'] && null === $context['locale']) {
                return $itemValue;
            }
            elseif ($context['locale'] && isset($itemValue[$context['locale']])) {
                return $itemValue[$context['locale']];
            }
        }

        return $itemValue;
    }
}