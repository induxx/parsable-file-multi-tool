<?php

namespace Misery\Component\Writer;

abstract class FileWriter implements ItemWriterInterface
{
    private ?string $tempPath;

    /** @var resource|null */
    private $handle;

    public function __construct(private readonly string $filePath, private readonly string $mode = 'wb+')
    {
        $dir = dirname($filePath);
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new Exception\IOException("Directory not writable: $dir");
        }
        $this->handle   = null;
        $this->tempPath = null;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * Write one “record” of data.
     *
     * @param array $data
     * @throws Exception\IOException
     */
    abstract public function write(array $data): void;

    /**
     * Ensures we have an open, locked handle on a temp file.
     *
     * @throws Exception\IOException
     */
    protected function openHandle(): void
    {
        if ($this->handle !== null) {
            return;
        }

        $uniq = uniqid('', true);
        $this->tempPath = $this->filePath . ".tmp.$uniq";

        $h = @fopen($this->tempPath, $this->mode);
        if ($h === false) {
            throw new Exception\IOException("Cannot open temp file for writing: {$this->tempPath}");
        }

        // Acquire exclusive lock
        if (!flock($h, LOCK_EX)) {
            fclose($h);
            @unlink($this->tempPath);
            throw new Exception\IOException("Cannot lock temp file: {$this->tempPath}");
        }

        $this->handle = $h;
    }

    /**
     * Writes raw bytes to the temp file.
     *
     * @param string $bytes
     * @throws Exception\IOException
     */
    protected function writeRaw(string $bytes): void
    {
        $this->openHandle();

        $written = fwrite($this->handle, $bytes);
        if ($written === false || $written < strlen($bytes)) {
            throw new Exception\IOException("Failed to write data to {$this->tempPath}");
        }
    }

    /**
     * Finalize: flush, fsync, close & rename into place.
     *
     * @throws Exception\IOException
     */
    public function close(): void
    {
        if (!$this->handle) {
            return;
        }

        // flush PHP buffers
        fflush($this->handle);

        // ensure data on disk
        if (function_exists('fsync')) {
            fsync($this->handle);
        }

        // release lock & close
        flock($this->handle, LOCK_UN);
        fclose($this->handle);

        // atomic rename
        if (!@rename($this->tempPath, $this->filePath)) {
            throw new Exception\IOException("Failed to rename {$this->tempPath} to {$this->filePath}");
        }

        $this->handle   = null;
        $this->tempPath = null;
    }

    public function __destruct()
    {
        try {
            $this->close();
        } catch (\Throwable $e) {
            // swallow on destruct
        }
    }
}