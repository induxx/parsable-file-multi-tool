<?php

declare(strict_types=1);

namespace Tests\Misery\Component\Converter\Akeneo\Csv;

use Misery\Component\Converter\Akeneo\Csv\Product;
use Misery\Component\Converter\AkeneoCsvHeaderContext;
use Misery\Component\Converter\Matcher;
use Misery\Component\Parser\CsvParser;
use Misery\Component\Parser\JsonParser;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    private function getAttributeTypes(): array
    {
        return [
            "aantal_personen_4_478" => "pim_catalog_number",
            "aantal_poten_8_857" => "pim_catalog_number",
            "abrasion_resistance_options" => "pim_catalog_simpleselect",
            // ... (truncated for brevity, add more as needed)
            "width" => "pim_catalog_metric",
            "yield" => "pim_catalog_textarea",
        ];
    }

    private function getTestCsvRow(int $i = 1): array
    {
        $file = __DIR__ . '/../../../../examples/coeck-quantified.csv';
        if (!file_exists($file)) {
            throw new \RuntimeException("Test CSV file not found: $file");
        }
        $parser = CsvParser::create($file);

        $parser->seek($i);

        return $parser->current(); // Use the first row for testing
    }

    private function getAkeneoProductPayload(int $i = 1): array
    {
        $file = __DIR__ . '/../../../../examples/akeneo_products_payload.jsonl';
        if (!file_exists($file)) {
            throw new \RuntimeException("Test Json payload file not found: $file");
        }
        $parser = JsonParser::create($file);

        $parser->seek($i);

        return $parser->current(); // Use the first row for testing
    }

    private function getProductConverter(array $options = []): Product
    {
        $defaults = [
            'attribute_types:list' => $this->getAttributeTypes(),
            'quantified_associations:keys' => ['uitklap'],
            'associations:keys' => ['vervang', 'PACK'],
            'identifier' => 'sku',
            'associations' => ['vervang', 'PACK'],
            'quantified_associations' => ['uitklap'],
        ];
        $options = array_merge($defaults, $options);
        $product = new Product(new AkeneoCsvHeaderContext());
        $product->setOptions($options);

        return $product;
    }

    public function testConvertWithAssociationsAndQuantifiedAssociations()
    {
        $converter = $this->getProductConverter();
        $row = $this->getTestCsvRow();

        $result = $converter->convert($row);
        $this->assertArrayHasKey('associations', $result);
        $this->assertArrayHasKey('PACK', $result['associations']);
        $this->assertArrayHasKey('products', $result['associations']['PACK']);
        $this->assertArrayNotHasKey('quantified_associations', $result);

        $row = $this->getTestCsvRow(3);

        $result = $converter->convert($row);
        $this->assertArrayHasKey('quantified_associations', $result);
        $this->assertArrayHasKey('uitklap', $result['quantified_associations']);
        $this->assertArrayHasKey('products', $result['quantified_associations']['uitklap']);
        $this->assertArrayNotHasKey('associations', $result);
    }

    public function testConvertWithoutAssociationsAndQuantifiedAssociations()
    {
        $converter = $this->getProductConverter([
            'quantified_associations:keys' => [],
            'associations:keys' => [],
            'associations' => [],
            'quantified_associations' => [],
        ]);
        $row = $this->getTestCsvRow();
        $result = $converter->convert($row);
        $this->assertArrayNotHasKey('associations', $result);
        $this->assertArrayNotHasKey('quantified_associations', $result);
    }

    public function testConvertWithoutAssociationsAndQuantifiedAssociationsInItem()
    {
        $converter = $this->getProductConverter();
        $row = $this->getTestCsvRow(4); # This row does not have associations or quantified associations
        $result = $converter->convert($row);
        $this->assertArrayNotHasKey('associations', $result);
        $this->assertArrayNotHasKey('quantified_associations', $result);
    }

    public function testRevertBasicSupport(): void
    {
        $converter = $this->getProductConverter([
            'attribute_types:list' => null, # optional, if not set, we assume all attributes are supported, and we look at the data type
        ]);

        $stdTestProduct = [
            'sku' => '0001',
            'enabled' => true,
            'categories' => ['1001', '1002'],
            'values|description' => [
                'locale' => 'nl_BE',
                'scope' => null,
                'data' =>  'Badkamerventilator met timer',
                'matcher' => Matcher::create('values|description'),
            ],
            'values|metric' => [
                'locale' => null,
                'scope' => null,
                'data' =>  [
                    'amount' => 0,
                    'unit' => 'METER',
                ],
                'matcher' => Matcher::create('values|metric'),
            ],
            'values|boolean' => [
                'locale' => null,
                'scope' => null,
                'data' => false,
                'matcher' => Matcher::create('values|boolean'),
            ],
        ];

        $expected = [
            'sku' => '0001',
            'enabled' => '1',
            'categories' => '1001,1002',
            'description' => 'Badkamerventilator met timer',
            'metric' => '0',
            'metric-unit' => 'METER',
            'boolean' => '0',
        ];

        $this->assertSame($expected, $converter->revert($stdTestProduct));

        $stdTestProduct['values|boolean']['data'] = true; // Change boolean to true

        $expected['boolean'] = '1'; // Update expected value for boolean

        $this->assertSame($expected, $converter->revert($stdTestProduct));
    }

    /**
     * Additional tests for Akeneo type conversions and missing attribute_types:list
     */
    public function testNumberConversionWithAttributeTypesList()
    {
        $converter = $this->getProductConverter();
        $row = [
            'aantal_personen_4_478' => '123,45',
        ];
        $result = $converter->convert($row);
        $this->assertIsNumeric($result['values|aantal_personen_4_478']['data']);
        $this->assertEquals('123.45', $result['values|aantal_personen_4_478']['data']);
    }

    public function testMetricConversionWithAttributeTypesList()
    {
        $converter = $this->getProductConverter();
        $row = [
            'width' => '10',
            'width-unit' => 'CM',
        ];
        $result = $converter->convert($row);
        $this->assertIsArray($result['values|width']['data']);
        $this->assertEquals(['amount' => '10', 'unit' => 'CM'], $result['values|width']['data']);
    }

    public function testBooleanConversionWithAttributeTypesList()
    {
        $types = $this->getAttributeTypes();
        $types['ecom_enabled'] = 'pim_catalog_boolean';
        $converter = $this->getProductConverter(['attribute_types:list' => $types]);
        $row = ['ecom_enabled' => '1'];
        $result = $converter->convert($row);
        $this->assertTrue($result['values|ecom_enabled']['data']);
        $row = ['ecom_enabled' => '0'];
        $result = $converter->convert($row);
        $this->assertFalse($result['values|ecom_enabled']['data']);
        $row = ['ecom_enabled' => ''];
        $result = $converter->convert($row);
        $this->assertNull($result['values|ecom_enabled']['data']);
    }

    public function testMultiselectConversionWithAttributeTypesList()
    {
        $types = $this->getAttributeTypes();
        $types['colors'] = 'pim_catalog_multiselect';
        $converter = $this->getProductConverter(['attribute_types:list' => $types]);
        $row = ['colors' => 'red,blue-green'];
        $result = $converter->convert($row);
        $this->assertIsArray($result['values|colors']['data']);
        $this->assertEquals(['red', 'blue_green'], $result['values|colors']['data']);
    }

    public function testSimpleselectConversionWithAttributeTypesList()
    {
        $types = $this->getAttributeTypes();
        $types['abrasion_resistance_options'] = 'pim_catalog_simpleselect';
        $converter = $this->getProductConverter(['attribute_types:list' => $types]);
        $row = ['abrasion_resistance_options' => 'option-1'];
        $result = $converter->convert($row);
        $this->assertEquals('option_1', $result['values|abrasion_resistance_options']['data']);
    }

    public function testReferenceDataMultiselectConversionWithAttributeTypesList()
    {
        $types = $this->getAttributeTypes();
        $types['ref_multi'] = 'pim_reference_data_multiselect';
        $converter = $this->getProductConverter(['attribute_types:list' => $types]);
        $row = ['ref_multi' => 'foo-bar,baz'];
        $result = $converter->convert($row);
        $this->assertEquals(['foo_bar', 'baz'], $result['values|ref_multi']['data']);
    }

    public function testReferenceDataSimpleselectConversionWithAttributeTypesList()
    {
        $types = $this->getAttributeTypes();
        $types['ref_simple'] = 'pim_reference_data_simpleselect';
        $converter = $this->getProductConverter(['attribute_types:list' => $types]);
        $row = ['ref_simple' => 'foo-bar'];
        $result = $converter->convert($row);
        $this->assertEquals('foo_bar', $result['values|ref_simple']['data']);
    }

    public function testPriceCollectionConversionWithAttributeTypesList()
    {
        $types = $this->getAttributeTypes();
        $types['price'] = 'pim_catalog_price_collection';
        $converter = $this->getProductConverter(['attribute_types:list' => $types, 'default_currency' => 'USD']);
        $row = ['price' => '99.99'];
        $result = $converter->convert($row);
        $this->assertEquals([['amount' => '99.99', 'currency' => 'USD']], $result['values|price']['data']);
    }

    public function testNoAttributeTypesListFallsBackToString()
    {
        $converter = $this->getProductConverter(['attribute_types:list' => null]);
        $row = ['foo' => 'bar'];
        $result = $converter->convert($row);
        $this->assertSame($row, $result);
    }

    public function testNoAttributeTypesListWithMetricLikeField()
    {
        $converter = $this->getProductConverter(['attribute_types:list' => null]);
        $row = ['width' => '10', 'width-unit' => 'CM'];
        $result = $converter->convert($row);
        $this->assertSame($row, $result);
    }
}
