<?php

namespace Misery\Component\Action;

use Misery\Model\DataStructure\ItemInterface;

class ItemActionProcessor
{
    public function __construct(private readonly array $configurationRules) {}

    public function process(ItemInterface|array $item): ItemInterface|array
    {
        if ($item instanceof ItemInterface) {
            foreach ($this->configurationRules as $action) {
                $action->applyAsItem($item);
            }
        }

        foreach ($this->configurationRules as $name => $action) {
            $item = $action->apply($item);
        }

        return $item;
    }
}