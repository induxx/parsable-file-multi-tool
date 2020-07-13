<?php

namespace Misery\Component\Source;

use Misery\Component\Parser\CsvParser;
use Misery\Component\Reader\ItemReader;

class Source
{
    private $type;
    private $input;
    private $readers;
    /**
     * @var string
     */
    private $alias;

    private function __construct(SourceType $type, string $input, string $alias)
    {
        $this->type = $type;
        $this->input = $input;
        $this->alias = $alias;
    }

    public static function promise(SourceType $type, string $input, string $alias)
    {
        return new self($type, $input, $alias);
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function read()
    {
        if (false === isset($this->readers[$this->input])) {
            if ($this->type->is('file')) {
                $this->readers[$this->input] = new ItemReader(CsvParser::create($this->input));
            }
        }

        return $this->readers[$this->input]->read();
    }

    public function write()
    {
        // TODO we know the file, the type so should be able to read / write from Source
    }
}