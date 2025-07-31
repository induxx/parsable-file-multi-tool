<?php

declare(strict_types=1);

namespace Misery\Component\Converter;

use Misery\Component\Akeneo\DataStructure\AkeneoItemBuilder;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Reader\ItemCollection;

class ObelinkPurchaseLoop implements ConverterInterface, RegisteredByNameInterface, OptionsInterface, ItemCollectionLoaderInterface
{
    use OptionsTrait;

    private $options = [
        'list' => null,
    ];

    public function load(array $item): ItemCollection
    {
        return new ItemCollection($this->convert($item));
    }

    public function convert(array $item): array
    {
        $result = [];

        $itemObj = AkeneoItemBuilder::fromProductApiPayload($item);

        $suppliers = 1;
        while($suppliers <= 5) {
            $supplierCode =  $itemObj->getItem('supplier_'.$suppliers.'_amount_order_factor')?->getDataValue();
            $supplierDeliverCode = $itemObj->getItem('supplier_'.$suppliers.'_deliver')?->getDataValue();

            if(!$supplierCode || !$supplierDeliverCode) {
                $suppliers++;
                continue;
            }

            $orderMultiplier = $itemObj->getItem('supplier_'.$suppliers.'_amount_order_factor')?->getDataValue();

            $result[] = $item + [
                'suppliers_data' => [
                    'supplier_index' => $suppliers,
                    'supplier_code' => $supplierCode,
                    'supplier_deliver_code' => $supplierDeliverCode,
                    'order_multiplier' => (int) $orderMultiplier,
                ],
            ];
            $suppliers++;
        }

        return $result;
    }

    public function revert(array $item): array
    {
        return $item;
    }

    public function getName(): string
    {
        return 'obelink/purchase/api';
    }
}