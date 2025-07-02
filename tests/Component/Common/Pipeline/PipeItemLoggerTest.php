<?php

namespace Tests\Misery\Component\Common\Pipeline;

use Misery\Component\Common\Pipeline\PipeItemLogger;
use Misery\Component\Logger\ItemLoggerInterface;
use PHPUnit\Framework\TestCase;

class PipeItemLoggerTest extends TestCase
{
    public function testWriteCallsLogCreateOnPost()
    {
        $logger = $this->createMock(ItemLoggerInterface::class);
        $logger->expects($this->once())
            ->method('logCreate')
            ->with('SomeClass', '123');

        $pipe = new PipeItemLogger($logger, 'SomeClass', 'POST');
        $pipe->write(['identifier' => '123']);
    }

    public function testWriteCallsLogUpdateOnPatch()
    {
        $logger = $this->createMock(ItemLoggerInterface::class);
        $logger->expects($this->once())
            ->method('logUpdate')
            ->with('SomeClass', 'abc');

        $pipe = new PipeItemLogger($logger, 'SomeClass', 'PATCH');
        $pipe->write(['code' => 'abc']);
    }

    public function testWriteCallsLogUpdateOnMultiPatch()
    {
        $logger = $this->createMock(ItemLoggerInterface::class);
        $logger->expects($this->once())
            ->method('logUpdate')
            ->with('SomeClass', 'sku-1');

        $pipe = new PipeItemLogger($logger, 'SomeClass', 'MULTI_PATCH');
        $pipe->write(['sku' => 'sku-1']);
    }

    public function testWriteCallsLogRemoveOnDelete()
    {
        $logger = $this->createMock(ItemLoggerInterface::class);
        $logger->expects($this->once())
            ->method('logRemove')
            ->with('SomeClass', 'id-42');

        $pipe = new PipeItemLogger($logger, 'SomeClass', 'DELETE');
        $pipe->write(['id' => 'id-42']);
    }

    public function testWriteCallsLogOnDefault()
    {
        $logger = $this->createMock(ItemLoggerInterface::class);
        $logger->expects($this->once())
            ->method('log')
            ->with('SomeClass', '');

        $pipe = new PipeItemLogger($logger, 'SomeClass', 'UNKNOWN');
        $pipe->write([]);
    }
}
