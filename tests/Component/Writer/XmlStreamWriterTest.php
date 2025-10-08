<?php

namespace Tests\Misery\Component\Writer;

use Misery\Component\Writer\XmlStreamWriter;
use PHPUnit\Framework\TestCase;
use Misery\Component\Parser\XmlParser;

class XmlStreamWriterTest extends TestCase
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
        $filename = tempnam(sys_get_temp_dir(), 'xmltest');
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
            'xpath' => 'Records|Record',
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
        $filename = tempnam(sys_get_temp_dir(), 'xmltest');
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
            'xpath' => 'PRODUCT_FEATURES',
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
        $filename = tempnam(sys_get_temp_dir(), 'xmltest');
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
            'xpath' => 'ORDER',
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
        $tmpFile = tempnam(sys_get_temp_dir(), 'xmltest');
        $input = [
            'PRODUCT_DETAILS' => [
                'DESCRIPTION_SHORT' => [
                    '@attributes' => ['dit' => '123', 'foo' => '456'],
                    '@value' => 'Short desc',
                ],
                'TOTAL' => '100.00',
            ]
        ];
        $writer = new \Misery\Component\Writer\XmlStreamWriter($tmpFile, [
            'root_container' => [
                'ORDERS' => [],
            ],
            'xpath' => 'PRODUCT_DETAILS',
            'split_attribute_nodes' => true,
        ]);
        $writer->write($input);
        $writer->close();

        $xml = file_get_contents($tmpFile);
        unlink($tmpFile);

        // Assert both DESCRIPTION_SHORT elements exist with correct attributes and value
        $this->assertStringContainsString('<DESCRIPTION_SHORT dit="123">Short desc</DESCRIPTION_SHORT>', $xml);
        $this->assertStringContainsString('<DESCRIPTION_SHORT foo="456">Short desc</DESCRIPTION_SHORT>', $xml);
        $this->assertStringContainsString('<TOTAL>100.00</TOTAL>', $xml);
    }

    public function testWriteNodeWithMultipleFValues(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'xmltest');
        $writer = new XmlStreamWriter($tmpFile, [
            'root_container' => [ 'ROOT' => [] ],
            'xpath' => 'ROOT|FEATURE',
        ]);

        $data = [
            'FNAME' => 'EF002356',
            'FVALUE' => ['0.2', '2.5'],
            'FUNIT' => 'UN',
        ];
        $writer->write($data);
        $writer->close();

        $xml = file_get_contents($tmpFile);
        unlink($tmpFile);

        $this->assertStringContainsString('<FNAME>EF002356</FNAME>', $xml);
        $this->assertStringContainsString('<FVALUE>0.2</FVALUE>', $xml);
        $this->assertStringContainsString('<FVALUE>2.5</FVALUE>', $xml);
        $this->assertStringContainsString('<FUNIT>UN</FUNIT>', $xml);
        // Ensure both FVALUE elements are present
        $this->assertEquals(2, substr_count($xml, '<FVALUE>'));
    }
}