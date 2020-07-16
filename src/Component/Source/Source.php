<?php

namespace Misery\Component\Source;

use Misery\Component\Common\Cursor\FunctionalCursor;
use Misery\Component\Parser\CsvParser;
use Misery\Component\Reader\ItemReader;
use Misery\Component\Reader\ItemReaderInterface;

class Source
{
    private $type;
    private $input;
    private $readers;
    private $alias;
    /** @var SourceCollection */
    private $collection;

    private function __construct(SourceType $type, string $input, string $alias)
    {
        $this->type = $type;
        $this->input = $input;
        $this->alias = $alias;
    }

    public function setCollection(SourceCollection $collection)
    {
        $this->collection = $collection;
    }

    public static function promise(SourceType $type, string $input, string $alias)
    {
        return new self($type, $input, $alias);
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getReader(): ItemReaderInterface
    {
        if (false === isset($this->readers[$this->input])) {
            if ($this->type->is('file')) {
                $this->readers[$this->input] = new ItemReader(new FunctionalCursor(CsvParser::create($this->input), function($item) {
                    return $this->collection->encode($item, $this->collection->createContextFromBluePrint($this->getAlias(). '.yaml'));
                }));
            }
        }

        return $this->readers[$this->input];
    }
}