<?php

namespace Tests\Misery\Component\Converter\Akeneo;

use Misery\Component\Converter\AkeneoFlatProductToCsvConverter;
use PHPUnit\Framework\TestCase;

class FlatProductToCsvConverterTest extends TestCase
{
    public function testRevertWithAssociationsAndQuantifiedAssociations()
    {
        $converter = new AkeneoFlatProductToCsvConverter();
        $converter->setOptions([
            'properties' => ['sku', 'associations', 'quantified_associations'],
        ]);

        $input = [
            'sku' => 'SKU123',
            'associations' => [
                'PACK' => [
                    'products' => ['157014'],
                ],
            ],
            'quantified_associations' => [
                'uitklap' => [
                    'products' => [
                        ['identifier' => '142802', 'quantity' => 180],
                        ['identifier' => '142809', 'quantity' => 2],
                    ],
                ],
            ],
        ];

        $result = $converter->revert($input);

        $this->assertEquals('157014', $result['PACK-products']);
        $this->assertEquals('142802,142809', $result['uitklap-products']);
        $this->assertEquals('180,2', $result['uitklap-products-quantity']);
        $this->assertEquals('SKU123', $result['sku']);
    }

    public function testRevertWithoutAssociationsAndQuantifiedAssociations()
    {
        $converter = new AkeneoFlatProductToCsvConverter();
        $converter->setOptions([
            'properties' => ['sku', 'associations', 'quantified_associations'],
        ]);

        $input = [
            'sku' => 'SKU456',
        ];

        $result = $converter->revert($input);

        $this->assertEquals('SKU456', $result['sku']);
        $this->assertArrayNotHasKey('PACK-products', $result);
        $this->assertArrayNotHasKey('uitklap-products', $result);
        $this->assertArrayNotHasKey('uitklap-products-quantity', $result);
    }
}

