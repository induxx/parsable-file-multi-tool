<?php

namespace Misery\Component\Logger;

use App\Component\Logger\ItemLogger;

class AppItemLoggerAdapter implements ItemLoggerInterface
{
    public function __construct(public readonly ItemLogger $logger) {}

    public function logCreate(string $entityClass, string $identifier): void
    {
        $this->logger->logCreate($entityClass, $identifier);
    }

    public function logUpdate(string $entityClass, string $identifier, string $updateMessage = ''): void
    {
        $this->logger->logUpdate($entityClass, $identifier, $updateMessage);
    }

    public function logRemove(string $entityClass, string $identifier): void
    {
        $this->logger->logRemove($entityClass, $identifier);
    }

    public function logFailed(string $entityClass, string $identifier, string $failMessage): void
    {
        $this->logger->logFailed($entityClass, $identifier, $failMessage);
    }

    public function log(string $entityClass, string $identifier): void
    {
        $this->logger->log($entityClass, $identifier);
    }
}