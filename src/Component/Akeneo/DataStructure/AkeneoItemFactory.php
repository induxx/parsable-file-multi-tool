<?php

namespace Misery\Component\Akeneo\DataStructure;

use Misery\Component\Converter\Matcher;
use Misery\Model\DataStructure\Item;
use Misery\Model\DataStructure\ItemInterface;

class AkeneoItemFactory
{
    public static function create(array $items, array $context = []): ItemInterface
    {
        $itemObj = new Item();
        foreach ($items as $code => $itemValue) {

            /** @var Matcher $matcher */
            /** Inject the type into the Item Value */
            $matcher = $itemValue['matcher'] ?? null;
            if ($matcher && $matcher->matches($code)) {
                $type = $context['attribute_types'][$matcher->getPrimaryKey()] ?? null;
                $itemValue['type'] = $type;
            }

            $itemObj->addItem($code, $itemValue, $context);
        }

        return $itemObj;
    }
}
