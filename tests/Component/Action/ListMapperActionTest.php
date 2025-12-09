<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\ListMapperAction;
use Misery\Component\Converter\Matcher;
use Misery\Model\DataStructure\Item;
use PHPUnit\Framework\TestCase;

class ListMapperActionTest extends TestCase
{
    public function testApplyWithMatchingValue()
    {
        $item = ['field' => 'value1'];

        $action = new ListMapperAction();
        $action->setOptions([
            'field' => 'field',
            'list' => ['value1' => 'new_value1']
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field' => 'new_value1'], $result);
    }

    public function testApplyWithNonMatchingValue()
    {
        $item = ['field' => 'value2'];

        $action = new ListMapperAction();
        $action->setOptions([
            'field' => 'field',
            'list' => ['value1' => 'new_value1']
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field' => 'value2'], $result);
    }

    public function testApplyWithEmptyList()
    {
        $item = ['field' => 'value1'];
        $action = new ListMapperAction();
        $action->setOptions([
            'field' => 'field',
            'list' => []
        ]);
        $result = $action->apply($item);
        $this->assertEquals(['field' => 'value1'], $result);
    }

    public function testApplyWithMissingFieldInItem()
    {
        $item = ['other_field' => 'value1'];
        $action = new ListMapperAction();
        $action->setOptions([
            'field' => 'field',
            'list' => ['value1' => 'new_value1']
        ]);
        $result = $action->apply($item);
        $this->assertEquals(['other_field' => 'value1'], $result);
    }

    public function testApplyWithNullValueInItem()
    {
        $item = ['field' => null];
        $action = new ListMapperAction();
        $action->setOptions([
            'field' => 'field',
            'list' => ['value1' => 'new_value1']
        ]);
        $result = $action->apply($item);
        $this->assertEquals(['field' => null], $result);
    }

    public function testApplyWithEmptyStringValueInItem()
    {
        $item = ['field' => ''];
        $action = new ListMapperAction();
        $action->setOptions([
            'field' => 'field',
            'list' => ['value1' => 'new_value1']
        ]);
        $result = $action->apply($item);
        $this->assertEquals(['field' => ''], $result);
    }

    public function testApplyAsItemCopiesMappedValuesToNewKeys(): void
    {
        $item = new Item('product');
        $matcher = Matcher::create('values|color', 'en_US', 'ecommerce');
        $value = [
            'data' => 'red',
            'matcher' => $matcher,
            'type' => 'text',
        ];
        $item->addItem($matcher->getMainKey(), $value, ['locale' => 'en_US', 'scope' => 'ecommerce']);

        $action = new ListMapperAction();
        $action->setOptions([
            'field' => 'values|color|en_US|ecommerce',
            'store_field' => 'values|color|en_US|ecommerce',
            'list' => ['red' => 'values|shade|en_US|ecommerce'],
        ]);

        $action->applyAsItem($item);

        $copied = $item->getItem('values|shade|en_US|ecommerce');
        $this->assertSame('red', $copied->getDataValue());
        $this->assertSame('text', $copied->getType());
    }
}
