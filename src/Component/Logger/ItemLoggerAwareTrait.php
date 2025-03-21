<?php

namespace Misery\Component\Logger;

use Psr\Log\LoggerInterface;

/**
 * Basic Implementation
 */
trait ItemLoggerAwareTrait
{
    /**
     * The logger instance.
     */
    protected ItemLoggerInterface $itemLogger;

    public function setItemLogger(ItemLoggerInterface $itemLogger): void
    {
        $this->itemLogger = $itemLogger;
    }

    public function getItemLogger(): ItemLoggerInterface
    {
        return $this->itemLogger;
    }
}
