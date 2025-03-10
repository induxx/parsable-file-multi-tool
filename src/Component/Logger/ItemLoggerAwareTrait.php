<?php

namespace Misery\Component\Logger;

use App\Bundle\AIBundle\Command\CommandItemLogger;
use Psr\Log\LoggerInterface;

/**
 * Basic Implementation
 */
trait ItemLoggerAwareTrait
{
    /**
     * The logger instance.
     */
    protected CommandItemLogger $itemLogger;

    public function setItemLogger(ItemLoggerInterface|CommandItemLogger $itemLogger): void
    {
        $this->itemLogger = $itemLogger;
    }

    public function getItemLogger(): ItemLoggerInterface|CommandItemLogger
    {
        return $this->itemLogger;
    }
}
