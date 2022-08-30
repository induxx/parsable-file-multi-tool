<?php

namespace Misery\Component\Tools;

use Misery\Component\Common\Utils\ValueFormatter;

class ContextFormatter
{
    public static function argument(string $argument, $context)
    {
        return ValueFormatter::format($argument, $context);
    }

    public static function arguments(array $args, array $context): array
    {
        return array_map(function ($argument) use ($context) {
            return static::argument($argument, $context);
        }, $args);
    }
}