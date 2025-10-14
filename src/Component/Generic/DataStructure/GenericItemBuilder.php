<?php

namespace Misery\Component\Generic\DataStructure;

use Misery\Component\Converter\Matcher;
use Misery\Model\DataStructure\Item;
use Misery\Model\DataStructure\ItemInterface;

class GenericItemBuilder
{
    public static function fromFlatData(array $itemData, array $context = []): ItemInterface
    {
        $itemObj = new Item('unclassified');

        foreach ($itemData as $property => $propertyValue) {
            if ($property === 'values') {
                continue;
            }
            if (is_array($propertyValue)) {
                $propertyValue['matcher'] = Matcher::create($property);
            }
            $itemObj->addItem($property, $propertyValue);
        }

        return $itemObj;
    }
}
