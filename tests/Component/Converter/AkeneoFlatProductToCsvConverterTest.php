<?php

namespace Tests\Misery\Component\Converter;

use Misery\Component\Converter\AkeneoFlatProductToCsvConverter;
use PHPUnit\Framework\TestCase;

class AkeneoFlatProductToCsvConverterTest extends TestCase
{
    public function testConvert()
    {
        $codes = [
            'sku' => 'pim_catalog_identifier',
            'product_number' => 'pim_catalog_number',
            'product_feature_1' => 'pim_catalog_textarea',
            'long_description' => 'pim_catalog_textarea',
            'exterior_finish_color' => 'pim_catalog_simpleselect',
            'exhaust_location' => 'pim_catalog_multiselect',
            "ship_gross_weight" => "pim_catalog_number",
            "product_weight" => "pim_catalog_metric",
        ];
        $converter = new AkeneoFlatProductToCsvConverter();
        $converter->setOptions([
            'attributes:list' => array_keys($codes),
            'attribute_types:list' => $codes,
            'localizable_attribute_codes:list' => [
                'long_description',
            ],
            'scopable_attribute_codes:list' => [
                'long_description',
            ],
            'default_metrics:list' => [
                'product_weight' ,
            ],
            'default_locale' => 'en_GB',
            'default_scope' => 'ecommerce',
            'default_currency' => 'EUR',
        ]);

        $input = [
            "sku" => "245999",
            "product_number" => "245999",
            "product_feature_1" => "Te combineren met decoratieve  dampkappen zonder motor;",
            "long_description" => "long-description",
            "exterior_finish_color" => "83",
            "exhaust_location" => "Boven,Rechts,Links,Voor,Achter",
            "ship_gross_weight" => "7,85",
            "product_weight" => "5,9",
            'dirty-data' => 'to-remove',
        ];

        $output = $converter->convert($input);

        $this->assertIsArray($output);
        $this->assertArrayHasKey('sku', $output);
        $this->assertEquals('245999', $output['sku']);
        $this->assertArrayHasKey('values|product_number', $output);
        $this->assertArrayNotHasKey('values|dirty-data', $output);

        $this->assertEquals('245999', $output['values|product_number']['data']);

        $this->assertEquals(['Boven', 'Rechts', 'Links', 'Voor', 'Achter'], $output['values|exhaust_location']['data']);

        $this->assertEquals('long-description', $output['values|long_description|en_GB|ecommerce']['data']);
    }
}
