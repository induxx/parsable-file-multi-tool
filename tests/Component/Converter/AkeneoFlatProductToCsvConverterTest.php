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

    public function testSimpleselectReferenceCodeNormalization()
    {
        $codes = [
            'exterior_finish_color' => 'pim_catalog_simpleselect',
        ];
        $converter = new AkeneoFlatProductToCsvConverter();
        $converter->setOptions([
            'attributes:list' => array_keys($codes),
            'attribute_types:list' => $codes,
            'reference_code' => true,
            'lower_cased' => true,
        ]);
        $input = [
            'exterior_finish_color' => 'COLOR-RED',
        ];
        $output = $converter->convert($input);
        $this->assertEquals('color_red', $output['values|exterior_finish_color']['data']);
    }

    public function testMultiselectReferenceCodeNormalization()
    {
        $codes = [
            'exhaust_location' => 'pim_catalog_multiselect',
        ];
        $converter = new AkeneoFlatProductToCsvConverter();
        $converter->setOptions([
            'attributes:list' => array_keys($codes),
            'attribute_types:list' => $codes,
            'reference_code' => true,
            'lower_cased' => true,
        ]);
        $input = [
            'exhaust_location' => 'Boven,Rechts,Links',
        ];
        $output = $converter->convert($input);
        $this->assertEquals(['boven', 'rechts', 'links'], $output['values|exhaust_location']['data']);
    }

    public function testSimpleselectReferenceCodeWithCustomPattern()
    {
        $codes = [
            'exterior_finish_color' => 'pim_catalog_simpleselect',
        ];
        $converter = new AkeneoFlatProductToCsvConverter();
        $converter->setOptions([
            'attributes:list' => array_keys($codes),
            'attribute_types:list' => $codes,
            'reference_code' => true,
            'lower_cased' => true,
            'reference_code_pattern' => 'new_pattern',
        ]);
        $input = [
            'exterior_finish_color' => 'COLOR-BLUE',
        ];
        $output = $converter->convert($input);
        $this->assertStringContainsString('color_blue', $output['values|exterior_finish_color']['data']);
    }

    public function testMultiselectReferenceCodeWithCustomPattern()
    {
        $codes = [
            'exhaust_location' => 'pim_catalog_multiselect',
        ];
        $converter = new AkeneoFlatProductToCsvConverter();
        $converter->setOptions([
            'attributes:list' => array_keys($codes),
            'attribute_types:list' => $codes,
            'reference_code' => true,
            'lower_cased' => true,
            'reference_code_pattern' => 'new_pattern',
        ]);
        $input = [
            'exhaust_location' => 'Boven,Rechts',
        ];

        $output = $converter->convert($input);
        $this->assertEquals('boven', $output['values|exhaust_location']['data'][0]);
        $this->assertEquals('rechts', $output['values|exhaust_location']['data'][1]);
    }

    public function testScopableAttributeWithoutLocaleUsesScope()
    {
        $codes = [
            'barcode' => 'pim_catalog_text',
        ];
        $converter = new AkeneoFlatProductToCsvConverter();
        $converter->setOptions([
            'attributes:list' => array_keys($codes),
            'attribute_types:list' => $codes,
            'scopable_attribute_codes:list' => ['barcode'],
            'default_scope' => 'print',
        ]);

        $input = [
            'barcode-ecommerce' => '12345',
        ];

        $output = $converter->convert($input);

        $this->assertArrayHasKey('values|barcode|ecommerce', $output);
        $this->assertNull($output['values|barcode|ecommerce']['locale']);
        $this->assertSame('ecommerce', $output['values|barcode|ecommerce']['scope']);
        $this->assertSame('12345', $output['values|barcode|ecommerce']['data']);
    }
}
