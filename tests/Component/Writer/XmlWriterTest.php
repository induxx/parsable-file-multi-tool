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
            'item_container' => 'Records|Record',
        ]);

        foreach ($this->items as $item) {
            $writer->write($item);
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

    public function test_write_repeated_elements(): void
    {
        $filename = __DIR__ . '/../../examples/STD_OUT';
        $input = [
            'PRODUCT_FEATURES' => [
                'FEATURE' => [
                    ['FNAME'=>'EF000007','FVALUE'=>'EV000202','FUNIT'=>'UN'],
                    ['FNAME'=>'EF002169','FVALUE'=>'EV000072','FUNIT'=>'UN'],
                ],
            ]
        ];

        $writer = new \Misery\Component\Writer\XmlStreamWriter($filename, [
            'root_container' => [
                'PRODUCT' => [],
            ],
            'item_container' => 'PRODUCT_FEATURES',
        ]);
        $writer->write($input);
        $writer->close();

        $parser = \Misery\Component\Parser\XmlParser::create($filename, 'FEATURE');
        $result = [];
        while ($item = $parser->current()) {
            $result[] = $item;
            $parser->next();
        }
        $expected = [
            ['FNAME'=>'EF000007','FVALUE'=>'EV000202','FUNIT'=>'UN'],
            ['FNAME'=>'EF002169','FVALUE'=>'EV000072','FUNIT'=>'UN'],
        ];
        $this->assertSame($expected, $result);
        $writer->clear();
    }

    public function test_write_nested_and_scalar_elements(): void
    {
        $filename = __DIR__ . '/../../examples/STD_OUT';
        $input = [
            'ORDER' => [
                '@attributes' => ['id' => '123'],
                'CUSTOMER' => [
                    'NAME' => 'John Doe',
                    'ADDRESS' => [
                        'STREET' => 'Main St',
                        'CITY' => 'Metropolis',
                    ],
                ],
                'TOTAL' => '100.00',
            ]
        ];
        $writer = new \Misery\Component\Writer\XmlStreamWriter($filename, [
            'root_container' => [
                'ORDERS' => [],
            ],
            'item_container' => 'ORDER',
        ]);
        $writer->write($input['ORDER']);
        $writer->close();

        $parser = \Misery\Component\Parser\XmlParser::create($filename, 'ORDER');
        $result = [];
        while ($item = $parser->current()) {
            $result[] = $item;
            $parser->next();
        }
        $expected = [$input['ORDER']];
        $this->assertSame($expected, $result);
        $writer->clear();
    }

    public function test_split_attribute_nodes(): void
    {
        $filename = __DIR__ . '/../../examples/STD_OUT';
        $input = [
            'PRODUCT_DETAILS' => [
                'DESCRIPTION_SHORT' => [
                    '@attributes' => ['dit' => '123'],
                ],
                'TOTAL' => '100.00',
            ]
        ];
        $writer = new \Misery\Component\Writer\XmlStreamWriter($filename, [
            'root_container' => [
                'ORDERS' => [],
            ],
            'item_container' => 'ORDER',
        ]);
    }
}