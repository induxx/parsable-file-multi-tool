<?php

namespace Tests\Misery\Component\Writer;

use Misery\Component\Writer\XmlStreamWriter;
use PHPUnit\Framework\TestCase;
use Misery\Component\Parser\XmlParser;

class XmlWriterTest extends TestCase
{
    private array $items = [
        [
            '@attributes' => [
                'id' => '1',
            ],
            'id' => '1',
            'first_name' => 'Gordie',
        ],
        [
            '@attributes' => [
                'id' => '2',
            ],
            'id' => "2",
            'first_name' => 'Frans',
        ],
    ];

    public function test_parse_xml_file(): void
    {
        $filename = __DIR__ . '/../../examples/STD_OUT';
        $writer = new XmlStreamWriter($filename, [
            'root_container' => [
                'PutRequest' => [
                    '@attributes' => [
                        'xmlns' => 'urn:xmlns:nedfox-retail3000api-com:putrequest',
                        'version' => '6.0',
                    ],
                ],
            ],
            'header_container' => [
                'HEADER' => [
                    '@attributes' => [
                        'version' => '6.0',
                    ],
                ],
            ],
            'item_container' => 'Records',
        ]);

        foreach ($this->items as $item) {
            $writer->write(['Record' => $item]);
        }

        $writer->close();

        $file = new \SplFileObject($filename);

        $this->assertTrue($file->isFile());

        $parser = XmlParser::create($filename, 'Record');

        $result = [];
        while ($item = $parser->current()) {
            $result[] = $item;
            $parser->next();
        }

        $this->assertSame($this->items, $result);

        $writer->clear();
    }
}