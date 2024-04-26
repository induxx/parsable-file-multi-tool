<?php

namespace Misery\Component\Statement;

class ItemPlaceholder
{
    public static function replace(string $text, array $item): array|string|null
    {
        return preg_replace_callback('/\{(\w+)\}/', function ($matches) use ($item) {
            // $matches[1] is the key inside the brackets
            return isset($item[$matches[1]]) ? $item[$matches[1]] : $matches[0];
        }, $text);
    }
}