<?php

namespace Misery\Component\Writer;

class JsonWriter implements ItemWriterInterface
{
    private $handle;
    private $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $this->handle = fopen($filename, 'wb+');
    }

    public function write(array $data): void
    {
        fwrite($this->handle, json_encode($data));
    }

    public function clear(): void
    {
        file_put_contents($this->filename, '');
    }

    public function close(): void
    {
        fclose($this->handle);
    }

    public function __destruct()
    {
        $this->close();
    }
}