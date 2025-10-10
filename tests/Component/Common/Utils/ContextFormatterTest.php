<?php

namespace Tests\Misery\Component\Common\Utils;

use Misery\Component\Common\Utils\ContextFormatter;
use PHPUnit\Framework\TestCase;

class ContextFormatterTest extends TestCase
{
    public function testContextFormat()
    {
        $context = [
            'locale' => 'en_US',
        ];
        $data = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => 'some_code',
                            'locale' => '%locale%',
                        ],
                        'return_value' => 'code',
                    ],
                ]
            ]
        ];
        $expectedResult = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => 'some_code',
                            'locale' => 'en_US',
                        ],
                        'return_value' => 'code',
                    ],
                ]
            ]
        ];

        $result = ContextFormatter::format($context, $data);

        $this->assertEquals($expectedResult, $result);
    }

    public function testContextFormatWithMissingValues()
    {
        $context = [
            'locale' => 'en_US',
        ];
        $data = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => '%code%',
                            'locale' => '%locale%',
                        ],
                        'return_value' => 'code',
                    ],
                ]
            ]
        ];
        $expectedResult = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => '%code%',
                            'locale' => 'en_US',
                        ],
                        'return_value' => 'code',
                    ],
                ]
            ]
        ];

        $result = ContextFormatter::format($context, $data);

        $this->assertEquals($expectedResult, $result);
    }

    public function testContextFormatWithMultipleValues()
    {
        $context = [
            'locale' => 'en_US',
            'code' => 'some_code',
        ];
        $data = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => '%code%',
                            'locale' => '%locale%',
                        ],
                        'return_value' => 'code',
                    ],
                ]
            ]
        ];
        $expectedResult = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => 'some_code',
                            'locale' => 'en_US',
                        ],
                        'return_value' => 'code',
                    ],
                ]
            ]
        ];

        $result = ContextFormatter::format($context, $data);

        $this->assertEquals($expectedResult, $result);
    }

    public function testContextFormatWithMultipleFoundValues()
    {
        $context = [
            'locale' => 'en_US',
            'code' => 'some_code',
        ];
        $data = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => '%code%',
                            'locale' => '%locale%',
                        ],
                        'return_value' => '%code%',
                    ],
                ]
            ]
        ];
        $expectedResult = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => 'some_code',
                            'locale' => 'en_US',
                        ],
                        'return_value' => 'some_code',
                    ],
                ]
            ]
        ];

        $result = ContextFormatter::format($context, $data);

        $this->assertEquals($expectedResult, $result);
    }

    public function testContextFormatWithMultipleFoundValuesAndKeys()
    {
        $context = [
            'locale' => 'en_US',
            'code' => 'some_code',
        ];
        $data = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => '%code%',
                            'locale' => '%locale%',
                        ],
                        'return_value' => '%code%',
                    ],
                ]
            ],
            'action' => [
                'first_action' => [
                    'context' => [
                        '%code%' =>  'code',
                    ]
                ]
            ]
        ];
        $expectedResult = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => 'some_code',
                            'locale' => 'en_US',
                        ],
                        'return_value' => 'some_code',
                    ],
                ]
            ],
            'action' => [
                'first_action' => [
                    'context' => [
                        'some_code' =>  'code',
                    ]
                ]
            ]
        ];

        $result = ContextFormatter::format($context, $data);

        $this->assertEquals($expectedResult, $result);
    }

    public function testContextFormatWithMultipleMixedValuesAndKeys()
    {
        $context = [
            'locale' => 'en_US',
            'code' => 'some_code',
            'idx', [],
            'filter' => null,
            "id_list" => ['1', '2', '3'],
        ];
        $data = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => '%code%',
                            'locale' => '%locale%',
                        ],
                        'return_value' => '%code%',
                    ],
                ]
            ],
            'actions' => [
                'first_action' => [
                    'context' => [
                        '%code%' =>  'code',
                    ]
                ],
                'second_action' => [
                    'id_list' => '%id_list%',
                    'filter' => '%filter%',
                ],
            ],
        ];
        $expectedResult = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => 'some_code',
                            'locale' => 'en_US',
                        ],
                        'return_value' => 'some_code',
                    ],
                ]
            ],
            'actions' => [
                'first_action' => [
                    'context' => [
                        'some_code' =>  'code',
                    ]
                ],
                'second_action' => [
                    'filter' => null,
                    'id_list' => ['1', '2', '3'],
                ],
            ]
        ];

        $result = ContextFormatter::format($context, $data);

        $this->assertEquals($expectedResult, $result);
    }

    public function testFormatWithScalarString()
    {
        $context = ['name' => 'World'];
        $data = 'Hello %name%!';
        $expected = 'Hello World!';
        $this->assertSame($expected, ContextFormatter::format($context, $data));
    }

    public function testFormatWithScalarInt()
    {
        $context = ['num' => 42];
        $data = 'Value: %num%';
        $expected = 'Value: 42';
        $this->assertSame($expected, ContextFormatter::format($context, $data));
    }

    public function testFormatWithMultipleSamePlaceholders()
    {
        $context = ['x' => 'A'];
        $data = '%x%-%x%-%x%';
        $expected = 'A-A-A';
        $this->assertSame($expected, ContextFormatter::format($context, $data));
    }

    public function testFormatWithPlaceholderKeyToNull()
    {
        $context = ['foo' => null];
        $data = ['%foo%' => 'bar'];
        $expected = [null => 'bar'];
        $this->assertEquals($expected, ContextFormatter::format($context, $data));
    }

    public function testFormatWithEmptyContext()
    {
        $data = ['foo' => '%bar%'];
        $expected = ['foo' => '%bar%'];
        $this->assertEquals($expected, ContextFormatter::format([], $data));
    }

    public function testFormatWithEmptyData()
    {
        $context = ['foo' => 'bar'];
        $this->assertEquals([], ContextFormatter::format($context, []));
    }

    public function testFormatWithNoPlaceholders()
    {
        $context = ['foo' => 'bar'];
        $data = ['hello' => 'world'];
        $expected = ['hello' => 'world'];
        $this->assertEquals($expected, ContextFormatter::format($context, $data));
    }
}
