<?php

namespace Misery\Component\Common\Client;

use Assert\Assert;

class MemoryContext
{
    private string $file;
    private array $data;

    public function __construct(string $file = '/tmp/memory_context.json')
    {
        Assert::that($file, 'File path must be a string')
            ->string()
            ->notEmpty()
            ->startsWith('/')
            ->endsWith('.json');

        $this->file = $file;
        $this->data = $this->getAll();
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function setWithExpire(string $key, mixed $value, int $ttl): void
    {
        throw new \RuntimeException('Method not implemented yet.');
    }

    public function delete(string $key): void
    {
        unset($this->data[$key]);
    }

    private function getAll(): array
    {
        if (!file_exists($this->file)) {
            return [];
        }

        $content = file_get_contents($this->file);
        return $content ? json_decode($content, true) ?? [] : [];
    }

    public function __destruct()
    {
        file_put_contents($this->file, json_encode($this->data));
    }
}