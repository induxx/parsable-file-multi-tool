<?php

namespace Misery\Component\Common\Runtime;

/**
 * Provides a per-run master key shared between components (parser, writer, identifier indexes, ...).
 */
final class RunKeyResolver
{
    private static ?string $runKey = null;

    /**
     * Resolve the current run key, optionally forcing an override.
     */
    public static function resolve(?string $override = null): string
    {
        if (is_string($override) && $override !== '') {
            self::$runKey = $override;
        } elseif (null === self::$runKey) {
            $envKey = $_ENV['MISERY_RUN_KEY'] ?? $_SERVER['MISERY_RUN_KEY'] ?? null;
            if (is_string($envKey) && $envKey !== '') {
                self::$runKey = $envKey;
            }
        }

        if (null === self::$runKey) {
            self::$runKey = bin2hex(random_bytes(10));
        }

        $_ENV['MISERY_RUN_KEY'] = self::$runKey;
        $_SERVER['MISERY_RUN_KEY'] = self::$runKey;

        return self::$runKey;
    }

    public static function reset(): void
    {
        self::$runKey = null;
    }
}
