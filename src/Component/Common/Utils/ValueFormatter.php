<?php

namespace Misery\Component\Common\Utils;

class ValueFormatter
{
    /**
     * format('%amount% %unit%', ['amount' => 1, 'unit' => 'GRAM']);
     * returns '1 GRAM'
     *
     * @param array $values
     * @param string $format
     *
     * @return string|string[]
     */
    public static function format(string $format, array $values)
    {
        $tmp = [];
        foreach (array_keys($values) as $value) {
            $tmp[] = "%$value%";
        }

        return str_replace($tmp, array_values($values), $format);
    }
}