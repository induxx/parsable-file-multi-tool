<?php

namespace Misery\Component\Converter\Akeneo\Api;

class CatalogLabelConverter
{
    public static function convert(array $item): array
    {
        // convert labels array to label-<locale-code> in a function
        if (isset($item['labels']) && is_array($item['labels'])) {
            foreach ($item['labels'] as $locale => $label) {
                $item['label-' . $locale] = $label;
            }
            unset($item['labels']);
        }

        return $item;
    }

    public static function revert(array $item): array
    {
        // revert label-<locale-code> to labels array in a function
        foreach ($item as $key => $value) {
            if (str_starts_with($key, 'label-')) {
                $locale = str_replace('label-', '', $key);
                $item['labels'][$locale] = $value;
                unset($item[$key]);
            }
        }

        return $item;
    }
}