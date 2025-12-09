<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\FirstValueAction;
use Misery\Component\Converter\Matcher;
use Misery\Model\DataStructure\Item;
use PHPUnit\Framework\TestCase;

class FirstValueActionTest extends TestCase
{
    public function testApplyAsItemStoresFirstNonEmptyValue(): void
    {
        $action = new FirstValueAction();
        $action->setOptions([
            'fields' => [
                'values|description|en_US|ecommerce',
                'values|name|en_US|ecommerce',
            ],
            'store_field' => 'values|summary|en_US|ecommerce',
            'default_value' => 'fallback',
        ]);

        $item = new Item('product');

        $descriptionMatcher = Matcher::create('values|description', 'en_US', 'ecommerce');
        $item->addItem($descriptionMatcher->getMainKey(), [
            'data' => '',
            'matcher' => $descriptionMatcher,
            'type' => 'text',
        ], ['locale' => 'en_US', 'scope' => 'ecommerce']);

        $nameMatcher = Matcher::create('values|name', 'en_US', 'ecommerce');
        $item->addItem($nameMatcher->getMainKey(), [
            'data' => 'Primary Name',
            'matcher' => $nameMatcher,
            'type' => 'text',
        ], ['locale' => 'en_US', 'scope' => 'ecommerce']);

        $action->applyAsItem($item);

        $copied = $item->getItem('values|summary|en_US|ecommerce');
        $this->assertSame('Primary Name', $copied->getDataValue());
        $this->assertSame('text', $copied->getType());
    }
}
