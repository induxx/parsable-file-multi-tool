<?php

namespace Misery\Component\Common\Pipeline;

use Misery\Component\Logger\ItemLoggerInterface;
use Misery\Component\Writer\ItemWriterInterface;

class PipeItemLogger implements PipeWriterInterface
{
    public function __construct(
        private readonly ItemLoggerInterface $itemLogger,
        private readonly string            $identityClass,
        private readonly string            $method
    ) {}

    public function write(array $data): void
    {
        $identifier = $data['identifier'] ?? $data['code'] ?? $data['sku'] ?? $data['id'] ?? '';

        // swith case for HTTP method type
        switch ($this->method) {
            case 'POST':
                $this->itemLogger->logCreate($this->identyClass, $identifier);
                break;
            case 'PATCH':
            case 'MULTI_PATCH':
                $this->itemLogger->logUpdate($this->identityClass, $identifier);
                break;
            case 'DELETE':
                $this->itemLogger->logRemove($this->identityClass, $identifier);
                break;
                break;
            default:
                $this->itemLogger->log($this->identityClass, $identifier);
        }
    }

    public function stop(): void
    {
    }
}