<?php

declare(strict_types=1);

namespace Tests\Misery\Component\Writer;

use Misery\Component\Writer\YamlWriter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class YamlWriterTest extends TestCase
{
    private string $filePath;

    protected function setUp(): void
    {
        $this->filePath = sys_get_temp_dir() . '/test_yaml_writer_' . uniqid() . '.yaml';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        }
    }

    public function test_it_writes_yaml_file_correctly(): void
    {
        $writer = new YamlWriter($this->filePath);
        $data1 = ['foo' => 'bar', 'baz' => 1];
        $data2 = ['hello' => 'world', 'baz' => 2];
        $writer->write($data1);
        $writer->write($data2);
        $writer->close();

        $this->assertFileExists($this->filePath);
        $contents = file_get_contents($this->filePath);
        $expected = Yaml::dump([$data1, $data2]);
        $this->assertSame(trim($expected), trim($contents));
    }
}

