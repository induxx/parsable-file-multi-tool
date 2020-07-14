<?php

namespace Misery\Component\Source;

use Misery\Component\Encoder\ItemEncoder;
use Symfony\Component\Yaml\Yaml;

class SourceCollection
{
    /** @var array */
    private $items = [];
    /** @var string */
    private $alias;
    /** @var string|null */
    private $bluePrintLocation;
    /** @var ItemEncoder */
    private $encoder;

    public function __construct(string $alias, ItemEncoder $encoder, string $bluePrintLocation = null)
    {
        $this->alias = $alias;
        $this->bluePrintLocation = $bluePrintLocation;
        $this->encoder = $encoder;
    }

    public function createContextFromBluePrint(string $file): array
    {
        return Yaml::parseFile(implode(DIRECTORY_SEPARATOR, [
                $this->bluePrintLocation,
                $this->alias,
                $file
        ]));
    }

    public function encode(array $item, array $context): array
    {
        return $this->encoder->encode($item, $context);
    }

    public function add(Source $source): void
    {
        $source->setCollection($this);
        $this->items[$source->getAlias()] = $source;
    }

    public function get($alias): ? Source
    {
        return $this->items[$alias] ?? null;
    }
}