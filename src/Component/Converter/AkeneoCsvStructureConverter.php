<?php

namespace Misery\Component\Converter;

class AkeneoCsvStructureConverter
{
    public static function convert(array $item, array $config)
    {
        $separator = '-';
        $narrow = [
            'codes' => ['code'],
        ];

        $output = [];
        foreach ($item as $key => $value) {
            if (\is_array($value) && \strpos($key, $separator) !== false) {
                $output[$key] = static::convert($value, $config);
            } elseif (is_string($value)) {
                $output[$key] = $value;
            }
        }

        return $output;
    }

    private function doConvert($item)
    {
        $oldKey = key($item);
        $key = explode('-', $oldKey);

        return [$key[0] => [
            'data' => $item[$oldKey],
            'locale' => $key[1] ?? null,
            'scope' => $key[2] ?? null,
        ]];
    }
}