<?php

namespace Misery\Component\Writer;

use Symfony\Component\Yaml\Yaml;

class YamlWriter extends FileWriter
{
    private array $items = [];

    public function write(array $data): void
    {
        $this->items[] = $data;
    }

    public function close(): void
    {
        $this->writeRaw(Yaml::dump($this->items));
        parent::close();
    }
}