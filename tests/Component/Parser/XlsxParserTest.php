<?php

namespace Tests\Misery\Component\Parser;

use Assert\Assert;
use Misery\Component\Parser\XlsxParser;
use PHPUnit\Framework\TestCase;

class XlsxParserTest extends TestCase
{
    private string $path = __DIR__ . '/../../examples/%s';

    public function testThrowsExceptionForMultipleSheets(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The file must contain exactly one sheet.');

        $parser = new XlsxParser(sprintf($this->path, 'multiple_sheets.xlsx'));
    }

    public function testThrowsExceptionForEmptyHeaders(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to read headers from the file: empty_headers.xlsx');

        $parser = new XlsxParser(sprintf($this->path, 'empty_headers.xlsx'));
    }

    public function testThrowsExceptionForMismatchedHeaderAndRowCount(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The number of headers does not match the number of values in the first row.');

        $parser = new XlsxParser(sprintf($this->path, 'mismatched_headers.xlsx'));
    }

    public function testAValidFile(): void
    {
        $parser = new XlsxParser(sprintf($this->path, 'valid_file.xlsx'));

        $this->assertTrue($parser->valid());
    }
}