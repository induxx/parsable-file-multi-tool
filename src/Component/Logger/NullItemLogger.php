<?php

declare(strict_types=1);

namespace Misery\Component\Logger;

class NullItemLogger implements ItemLoggerInterface
{
    public function logCreate(string $entityClass, string $identifier): void
    {
        // Do nothing
    }

    public function logUpdate(string $entityClass, string $identifier, string $updateMessage = ''): void
    {
        // Do nothing
    }

    public function logRemove(string $entityClass, string $identifier): void
    {
        // Do nothing
    }

    public function logFailed(string $entityClass, string $identifier, string $failMessage): void
    {
        // Do nothing
    }

    public function log(string $entityClass, string $identifier): void
    {
        // Do nothing
    }
}
