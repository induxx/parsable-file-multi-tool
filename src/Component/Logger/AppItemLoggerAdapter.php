<?php

namespace Misery\Component\Logger;

use App\Component\ItemTracker\ItemTrackerInterface;
use App\Component\Logger\ItemLogger;

class AppItemLoggerAdapter implements ItemLoggerInterface
{
    public function __construct(private readonly ItemTrackerInterface $tracker) {}

    public function logCreate(string $entityClass, string $identifier): void
    {
        $this->tracker->trackCreate($entityClass, $identifier);
    }

    public function logUpdate(string $entityClass, string $identifier, string $updateMessage = ''): void
    {
        $this->tracker->trackUpdate($entityClass, $identifier, $updateMessage);
    }

    public function logRemove(string $entityClass, string $identifier): void
    {
        $this->tracker->trackRemove($entityClass, $identifier);
    }

    public function logFailed(string $entityClass, string $identifier, string $failMessage): void
    {
        $this->tracker->trackFailed($entityClass, $identifier, $failMessage);
    }

    public function logSkipped(string $entityClass, string $identifier, string $failMessage): void
    {
        $this->tracker->trackSkipped($entityClass, $identifier, $failMessage);
    }

    public function log(string $entityClass, string $identifier): void
    {
        $this->tracker->track($entityClass, $identifier);
    }
}