<?php

namespace Tests\Misery\Component\Converter;

use Misery\Component\Converter\AkeneoCsvStructureConverter;
use PHPUnit\Framework\TestCase;

class AkeneoCsvStructureConverterTest extends TestCase
{
    private $items = [
        [
            'sku' => '13445',
            'code-nl_BE-ecom' => 'value',
            'code-nl_BE-print' => 'value-print',
            'code-fr_BE-print' => 'value-print',
        ],
    ];

    public function test_parse_csv_file(): void
    {
        $item = $this->items[0];

        $item = AkeneoCsvStructureConverter::convert($item, ['code']);

        dump($item);
    }
}