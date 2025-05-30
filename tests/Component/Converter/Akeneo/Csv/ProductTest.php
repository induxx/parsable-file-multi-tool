<?php

declare(strict_types=1);

namespace Tests\Misery\Component\Converter\Akeneo\Csv;

use Misery\Component\Converter\Akeneo\Csv\Product;
use Misery\Component\Converter\AkeneoCsvHeaderContext;
use Misery\Component\Parser\CsvParser;
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
}
