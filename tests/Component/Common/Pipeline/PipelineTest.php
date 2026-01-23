<?php

declare(strict_types=1);

namespace Tests\Misery\Component\Common\Pipeline;

use Misery\Component\Common\Pipeline\Pipeline;
use Misery\Component\Common\Pipeline\PipeReaderInterface;
use Misery\Component\Common\Pipeline\PipeWriterInterface;
use Misery\Component\Common\Pipeline\PipeInterface;
use Misery\Component\Common\Pipeline\Exception\InvalidItemException;
use Misery\Component\Common\Pipeline\Exception\SkipPipeLineException;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;

class DummyReader implements PipeReaderInterface
{
    private $items;
    public function __construct(array $items) { $this->items = $items; }
    public function read() { return array_shift($this->items); }
    public function stop(): void {}
}

class DummyWriter implements PipeWriterInterface
{
    public $written = [];
    public function write(array $data): void { $this->written[] = $data; }
    public function stop(): void {}
}

class DummyPipe implements PipeInterface
{
    public function pipe(array $item): array { $item['processed'] = true; return $item; }
}

class ExceptionPipe implements PipeInterface
{
    public function pipe(array $item): array { throw new InvalidItemException('Invalid!', $item); }
}

class ExceptionPipeWithIdentity implements PipeInterface
{
    public function pipe(array $item): array
    {
        throw new InvalidItemException(
            'Invalid!',
            [
                'identityClass' => 'Product',
                'identifier' => 123,
            ],
            $item
        );
    }
}

class SkipPipe implements PipeInterface
{
    public function pipe(array $item): array
    {
        throw new SkipPipeLineException('Nope');
    }
}

class TestLogger implements LoggerInterface
{
    public array $records = [];

    public function emergency($message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        $this->records[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];
    }
}

class PipelineTest extends TestCase
{
    public function test_pipeline_processes_items_and_outputs(): void
    {
        $reader = new DummyReader([
            ['id' => 1],
            ['id' => 2],
        ]);
        $writer = new DummyWriter();
        $logger = new TestLogger();
        $pipeline = new Pipeline();
        $pipeline->setLogger($logger);
        $pipeline->input($reader)
            ->line(new DummyPipe())
            ->output($writer);
        $pipeline->run();
        $this->assertCount(2, $writer->written);
        $this->assertTrue($writer->written[0]['processed']);
        $this->assertEquals(1, $writer->written[0]['id']);
    }

    public function test_pipeline_handles_invalid_items(): void
    {
        $reader = new DummyReader([
            ['id' => 1],
        ]);
        $writer = new DummyWriter();
        $invalid = new DummyWriter();
        $logger = new TestLogger();
        $pipeline = new Pipeline();
        $pipeline->setLogger($logger);
        $pipeline->input($reader)
            ->line(new ExceptionPipe())
            ->invalid($invalid)
            ->output($writer);
        $pipeline->run();
        $this->assertCount(0, $writer->written);
        $this->assertCount(1, $invalid->written);
        $this->assertStringContainsString('Invalid!', $invalid->written[0]['msg']);
        $this->assertStringContainsString('id', $invalid->written[0]['item']);
    }

    public function test_pipeline_logs_warning_for_invalid_items(): void
    {
        $reader = new DummyReader([
            ['id' => 1],
        ]);
        $invalid = new DummyWriter();
        $logger = new TestLogger();
        $pipeline = new Pipeline();
        $pipeline->setLogger($logger);
        $pipeline->input($reader)
            ->line(new ExceptionPipeWithIdentity())
            ->invalid($invalid);
        $pipeline->run();

        $this->assertCount(1, $logger->records);
        $this->assertSame('warning', $logger->records[0]['level']);
        $this->assertSame('Invalid!', $logger->records[0]['message']);
        $this->assertSame([
            'line' => 1,
            'identityClass' => 'Product',
            'identifier' => 123,
        ], $logger->records[0]['context']);
    }

    public function test_pipeline_logs_warning_for_skipped_items(): void
    {
        $reader = new DummyReader([
            ['id' => 1],
        ]);
        $writer = new DummyWriter();
        $logger = new TestLogger();
        $pipeline = new Pipeline();
        $pipeline->setLogger($logger);
        $pipeline->input($reader)
            ->line(new SkipPipe())
            ->output($writer);
        $pipeline->run();

        $this->assertCount(0, $writer->written);
        $this->assertCount(1, $logger->records);
        $this->assertSame('info', $logger->records[0]['level']);
        $this->assertSame('Skipped: Nope', $logger->records[0]['message']);
        $this->assertSame([
            'line' => 1,
        ], $logger->records[0]['context']);
    }
}
