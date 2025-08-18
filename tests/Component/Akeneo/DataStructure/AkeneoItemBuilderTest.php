<?php

namespace Tests\Misery\Component\Akeneo\DataStructure;

use Misery\Component\Akeneo\DataStructure\AkeneoItemBuilder;
use Misery\Component\Converter\Matcher;
use Misery\Model\DataStructure\ItemInterface;
use PHPUnit\Framework\TestCase;

class AkeneoItemBuilderTest extends TestCase
{
    private array $productData = [
        'identifier' => '1234',
        'parent' => '5678',
        'categories' => ['category1', 'category2'],
        'values' => [
            'weight' => [
                [
                    'locale' => null,
                    'scope' => null,
                    'data' => [
                        'amount' => 1,
                        'unit' => 'GRAM'
                    ]
                ]
            ],
            'color' => [
                [
                    'locale' => 'en_US',
                    'scope' => 'ecom',
                    'data' => 'red',
                ]
            ],
            'brand' => [
                [
                    'locale' => 'en_US',
                    'scope' => null,
                    'data' => 'nike'
                ]
            ],
        ],
    ];

    public function testFromProductApiPayload(): void
    {
        $productData = $this->productData;

        $context = [
            'attribute_types' => [
                'weight' => 'metric',
                'color' => 'simple_select',
                'brand' => 'reference_data',
            ],
        ];

        // Call the method
        $item = AkeneoItemBuilder::fromProductApiPayload($productData, $context);

        // Assertions
        $this->assertInstanceOf(ItemInterface::class, $item);

        $this->assertSame(['identifier', 'parent', 'categories', 'values|weight', 'values|color|en_US|ecom', 'values|brand|en_US'], $item->getItemCodes());
        $colorData = $item->getItem('values|color|en_US|ecom')->getValue();

        $this->assertEquals(
            [
                'locale' => 'en_US',
                'scope' => 'ecom',
                'type' => 'simple_select',
                'matcher' => Matcher::create('values|color', 'en_US', 'ecom'),
                'data' => 'red',
            ],
            $colorData
        );

        $weightData = $item->getItem('values|weight')->getValue();

        $this->assertEquals(
            [
                'locale' => null,
                'scope' => null,
                'type' => 'metric',
                'matcher' => Matcher::create('values|weight'),
                'data' => [
                    'amount' => 1,
                    'unit' => 'GRAM'
                ],
            ],
            $weightData
        );

        $categories = $item->getItem('categories')->getValue();

        $this->assertEquals(
            [
                'category1',
                'category2',
            ],
            $categories
        );
    }

    public function testTwoDirectionalLoads()
    {
        $productData = $this->productData;

        $context = [
            'attribute_types' => [
                'weight' => 'metric',
                'color' => 'simple_select',
                'brand' => 'reference_data',
            ],
        ];

        // Call the method
        $item = AkeneoItemBuilder::fromProductApiPayload($productData, $context);

        $this->assertSame($productData, $item->toArray());
    }

    public function testFromCatalogApiPayload(): void
    {
        $catalogData = [
            'code' => 'black',
            'labels' => [
                'nl_BE' => 'zwart',
                'en_US' => 'black',
            ]
        ];

        // Call the method
        $item = AkeneoItemBuilder::fromCatalogApiPayload($catalogData, ['class' => 'attribute']);

        // Assertions
        $this->assertInstanceOf(ItemInterface::class, $item);

        $this->assertSame(['code', 'labels|nl_BE', 'labels|en_US'], $item->getItemCodes());

        $this->assertSame('black', $item->getItem('labels|en_US')->getDataValue());
        $this->assertSame('zwart', $item->getItem('labels|nl_BE')->getDataValue());
    }
}
