<?php

declare(strict_types=1);

namespace Tests\Misery\Component\Common\Pipeline;

use Misery\Component\Common\Pipeline\Pipeline;
use Misery\Component\Common\Pipeline\PipeReaderInterface;
use Misery\Component\Common\Pipeline\PipeWriterInterface;
use Misery\Component\Common\Pipeline\PipeInterface;
use Misery\Component\Common\Pipeline\Exception\InvalidItemException;
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

class PipelineTest extends TestCase
{
    public function test_pipeline_processes_items_and_outputs(): void
    {
        $reader = new DummyReader([
            ['id' => 1],
            ['id' => 2],
        ]);
        $writer = new DummyWriter();
        $pipeline = new Pipeline();
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
        $pipeline = new Pipeline();
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
}
