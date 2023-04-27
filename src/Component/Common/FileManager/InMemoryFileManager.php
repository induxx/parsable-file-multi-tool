<?php

namespace Misery\Component\Common\FileManager;

use Assert\Assert;

class InMemoryFileManager implements FileManagerInterface
{
    public const GROUP_SOURCES = 'sources';
    public const GROUP_SOURCES_EXTRA = 'sources-extra';
    public const GROUP_WORKPATH = 'workpath';
    public const GROUP_ALIAS = 'alias';

    private $files;
    private $aliases = [];

    public static function createFromFileManager(FileManagerInterface $fileManager, string $group): self
    {
        $fm = new self();
        $fm->addFiles($fileManager->listFiles(), $group);

        return $fm;
    }

    public function addAliases(array $aliases): void
    {
        $this->aliases = array_merge($this->aliases, $aliases);

        foreach ($this->files as $group => $files) {
            foreach ($aliases as $aliasName => $pattern) {
                $matches = array_filter(array_keys($files), function($key) use ($pattern) {
                    return fnmatch(pathinfo($pattern, PATHINFO_BASENAME), $key);
                });
                if (count($matches) === 1) {
                    $this->files[self::GROUP_ALIAS][$aliasName] = $this->files[$group][current($matches)];
                }
            }
        }
    }

    public function addFromFileManager(FileManagerInterface $sourceCollection, string $group): void
    {
        $this->addFiles($sourceCollection->listFiles(), $group);
    }

    public function getFile(string $filename, string $group = null): string
    {
        if (is_string($group)) {
            $file = $this->files[$group][$filename] ?? null;
        } else {
            $file =
                $this->files[self::GROUP_SOURCES][$filename] ??
                $this->files[self::GROUP_SOURCES_EXTRA][$filename] ??
                $this->files[self::GROUP_ALIAS][$filename] ??
                $this->files[self::GROUP_WORKPATH][$filename] ??
                $this->aliases[$filename] ??
                null
            ;
        }

        if ($file) {
            return $file;
        }

        throw new \Exception(sprintf('File %s not found', $filename));
    }

    public function addFiles($files, string $group): void
    {
        Assert::that($files)->isTraversable();

        foreach ($files as $file) {
            Assert::that($file)->file();
            $this->files[$group][pathInfo($file, PATHINFO_BASENAME)] = $file;
        }
    }

    public function addFile(string $filename, $content)
    {
        // TODO: Implement addFile() method.
    }

    public function getFileContent(string $filename)
    {
        // TODO: Implement getFileContent() method.
    }

    public function removeFile(string $filename): void
    {
        // TODO: Implement removeFile() method.
    }

    public function removeFiles(...$filenames): void
    {
        // TODO: Implement removeFiles() method.
    }

    public function isFile(string $filename): bool
    {
        // TODO: Implement isFile() method.
    }

    public function listFiles(string $group = null): \Generator
    {
        if (is_string($group)) {
            $files = $this->files[$group] ?? [];
        } else {
            $files = array_merge(...array_values($this->files));
        }

        foreach ($files as $alias => $file) {
            if (is_file($file)) {
                yield $alias => $file;
            }
        }
    }

    public function clear(): void
    {
        // TODO: Implement clear() method.
    }

    public function provisionPath(string $filename): string
    {
    }
}