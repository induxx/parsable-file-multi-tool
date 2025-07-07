<?php

namespace Misery\Component\Writer;

class JsonlWriter extends FileWriter
{
    /**
     * Construct and open the target file in append mode.
     *
     * @param string $filename
     * @param bool   $append   If true, use 'a'; otherwise overwrite with 'w'
     * @throws \RuntimeException if handle cannot be opened
     */
    public function __construct(string $filename, bool $append = false)
    {
        $mode = $append ? 'a' : 'wb+';
        parent::__construct($filename, $mode);
    }

    /**
     * Write one record as a JSON object on its own line.
     *
     * @param array<mixed> $data
     * @return void
     * @throws \JsonException on encoding failure
     */
    public function write(array $data): void
    {
        // Force exceptions for errors, preserve Unicode
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        // Ensure exactly one newline terminator per record
        $this->writeRaw($json. PHP_EOL);
    }
}