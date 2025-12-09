<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\CopyAction;
use Misery\Component\Converter\Matcher;
use Misery\Model\DataStructure\Item;
use PHPUnit\Framework\TestCase;

class CopyActionTest extends TestCase
{
    public function test_it_should_do_a_copy_action(): void
    {
        $format = new CopyAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'from' => 'brand',
            'to' => 'merk',
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
            'merk' => 'louis',
        ], $format->apply($item));
    }

    public function test_it_copies_value_items_when_used_on_item_objects(): void
    {
        $action = new CopyAction();
        $action->setOptions([
            'from' => 'values|color|en_US|ecommerce',
            'to' => 'values|secondary_color|en_US|ecommerce',
        ]);

        $item = new Item('product');
        $matcher = Matcher::create('values|color', 'en_US', 'ecommerce');
        $value = [
            'data' => 'red',
            'locale' => 'en_US',
            'scope' => 'ecommerce',
            'matcher' => $matcher,
            'type' => 'text',
        ];
        $item->addItem($matcher->getMainKey(), $value, ['locale' => 'en_US', 'scope' => 'ecommerce']);

        $action->applyAsItem($item);

        $copied = $item->getItem('values|secondary_color|en_US|ecommerce');
        $this->assertSame('red', $copied->getDataValue());
        $this->assertSame('text', $copied->getType());
    }
}
