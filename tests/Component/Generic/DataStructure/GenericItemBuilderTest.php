<?php

declare(strict_types=1);

namespace Tests\Misery\Component\Generic\DataStructure;

use Misery\Component\Generic\DataStructure\GenericItemBuilder;
use Misery\Model\DataStructure\ItemInterface;
use PHPUnit\Framework\TestCase;

class GenericItemBuilderTest extends TestCase
{
    public function test_fromFlatData_builds_item_with_scalar_and_array_properties(): void
    {
        $input = [
            'sku' => '123',
            'name' => 'Test Product',
            'attributes' => ['color' => 'red', 'size' => 'L'],
            'values' => ['should' => 'be skipped'],
        ];

        $item = GenericItemBuilder::fromFlatData($input);
        $this->assertInstanceOf(ItemInterface::class, $item);
        $this->assertSame('123', $item->getItem('sku')->getValue());
        $this->assertSame('Test Product', $item->getItem('name')->getValue());
        $attributes = $item->getItem('attributes')->getValue();
        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('color', $attributes);
        $this->assertArrayHasKey('size', $attributes);
        $this->assertArrayHasKey('matcher', $attributes, 'Matcher should be added to array properties');
        $this->assertNull($item->getItem('values'), 'The "values" property should be skipped');
    }

    public function test_fromFlatData_empty_input(): void
    {
        $item = GenericItemBuilder::fromFlatData([]);
        $this->assertInstanceOf(ItemInterface::class, $item);
        $this->assertEmpty($item->getItemNodes());
    }
}
