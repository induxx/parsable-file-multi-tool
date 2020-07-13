<?php

namespace Misery\Component\Source;

class SourceCollection
{
    /** @var array */
    private $items = [];

    public function add(Source $source): void
    {
        $this->items[$source->getAlias()] = $source;
    }

    public function get($alias): ? Source
    {
        return $this->items[$alias] ?? null;
    }
}