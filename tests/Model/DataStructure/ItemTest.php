<?php

namespace Tests\Misery\Model\DataStructure;

use Misery\Component\Converter\Matcher;
use Misery\Model\DataStructure\Item;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    public function testItCopiesScalarPropertyValues(): void
    {
        $item = new Item('product');
        $item->addItem('sku', 'SKU-001');

        $item->copyItem('sku', 'ean');

        $copied = $item->getItem('ean');
        $this->assertNotNull($copied);
        $this->assertSame('SKU-001', $copied->getValue());
        $this->assertSame('ean', $copied->getMatcher()->getMainKey());
        $this->assertSame('property', $copied->getType());
    }

    public function testItCopiesMatcherValuesToPlainProperties(): void
    {
        $item = new Item('product');
        $matcher = Matcher::create('values|description', 'en_US', 'ecommerce');
        $value = [
            'data' => 'A red sneaker',
            'matcher' => $matcher,
            'type' => 'text',
        ];

        $item->addItem($matcher->getMainKey(), $value, ['locale' => 'en_US', 'scope' => 'ecommerce']);

        $item->copyItem($matcher->getMainKey(), 'short_description');

        $copied = $item->getItem('short_description');
        $this->assertNotNull($copied);
        $this->assertSame('A red sneaker', $copied->getDataValue());
        $this->assertSame('short_description', $copied->getMatcher()->getMainKey());
        $this->assertSame('property', $copied->getType());
        $this->assertSame('en_US', $copied->getContext()['locale']);
        $this->assertSame('ecommerce', $copied->getContext()['scope']);
    }

    public function testItCopiesMatcherValuesToNewMatcherTargets(): void
    {
        $item = new Item('product');
        $matcher = Matcher::create('values|name', 'en_US', 'ecommerce');
        $value = [
            'data' => 'Name',
            'matcher' => $matcher,
            'type' => 'text',
        ];
        $item->addItem($matcher->getMainKey(), $value, ['locale' => 'en_US', 'scope' => 'ecommerce']);

        $item->copyItem($matcher->getMainKey(), 'values|short_name|en_US|ecommerce');

        $copied = $item->getItem('values|short_name|en_US|ecommerce');
        $this->assertNotNull($copied);
        $this->assertSame('Name', $copied->getDataValue());
        $this->assertSame('values|short_name|en_US|ecommerce', $copied->getMatcher()->getMainKey());
        $this->assertSame('text', $copied->getType());
    }
}
