<?php

namespace Tests\Misery\Component\Converter\Akeneo;

use Misery\Component\Converter\Akeneo\AkeneoOptionExtractor;
use Misery\Component\Reader\ItemCollection;
use PHPUnit\Framework\TestCase;

class AkeneoOptionExtractorTest extends TestCase
{
    public function testConvert()
    {
        $extractor = new AkeneoOptionExtractor();
        $extractor->setOptions([
            'attribute_option_codes:list' => ['color', 'size', 'brand'],
        ]);

        $item = [
            'color' => ['Red', 'Blue'],
            'size' => ['L', 'M'],
            'brand' => 'nike',
        ];

        $expected = [
            'red-color' => [
                'attribute' => 'color',
                'code' => 'red',
                'reference' => 'red-color',
                'original_value' => 'Red'
            ],
            'blue-color' => [
                'attribute' => 'color',
                'code' => 'blue',
                'reference' => 'blue-color',
                'original_value' => 'Blue'
            ],
            'l-size' => [
                'attribute' => 'size',
                'code' => 'l',
                'reference' => 'l-size',
                'original_value' => 'L'
            ],
            'm-size' => [
                'attribute' => 'size',
                'code' => 'm',
                'reference' => 'm-size',
                'original_value' => 'M'
            ],
            'nike-brand' => [
                'attribute' => 'brand',
                'code' => 'nike',
                'reference' => 'nike-brand',
                'original_value' => 'nike'
            ]
        ];


        $this->assertEquals($expected, $extractor->convert($item));
    }

    public function testLoad()
    {
        $extractor = new AkeneoOptionExtractor();
        $extractor->setOptions([
            'attribute_option_codes:list' => ['color', 'size'],
            'parent_identifier' => 'attribute',
            'identifier' => 'code',
            'reference' => 'reference',
            'reference_code' => true,
            'lower_cased' => true,
        ]);

        $item = [
            'color' => ['Red', 'Blue'],
            'size' => ['L', 'M'],
        ];

        $expected = new ItemCollection([
            'red-color' => [
                'attribute' => 'color',
                'code' => 'red',
                'reference' => 'red-color',
                'original_value' => 'Red'
            ],
            'blue-color' => [
                'attribute' => 'color',
                'code' => 'blue',
                'reference' => 'blue-color',
                'original_value' => 'Blue'
            ],
            'l-size' => [
                'attribute' => 'size',
                'code' => 'l',
                'reference' => 'l-size',
                'original_value' => 'L'
            ],
            'm-size' => [
                'attribute' => 'size',
                'code' => 'm',
                'reference' => 'm-size',
                'original_value' => 'M'
            ],
        ]);

        $this->assertEquals($expected, $extractor->load($item));
    }

    public function testReferenceCodeFalseDisablesModification()
    {
        $extractor = new AkeneoOptionExtractor();
        $extractor->setOptions([
            'attribute_option_codes:list' => ['color'],
            'reference_code' => false,
            'lower_cased' => true,
        ]);
        $item = ['color' => ['Red', 'Blue']];
        $expected = [
            'red-color' => [
                'attribute' => 'color',
                'code' => 'red',
                'reference' => 'red-color',
                'original_value' => 'Red',
            ],
            'blue-color' => [
                'attribute' => 'color',
                'code' => 'blue',
                'reference' => 'blue-color',
                'original_value' => 'Blue',
            ],
        ];
        $this->assertEquals($expected, $extractor->convert($item));
    }

    public function testReferenceCodePatternCustomPattern()
    {
        $extractor = new AkeneoOptionExtractor();
        $extractor->setOptions([
            'attribute_option_codes:list' => ['brand'],
            'reference_code' => true,
            'reference_code_pattern' => 'new_pattern',
            'lower_cased' => true,
        ]);
        $item = ['brand' => 'Nike'];
        $expected = [
            'nike-brand' => [
                'attribute' => 'brand',
                'code' => 'nike',
                'reference' => 'nike-brand',
                'original_value' => 'Nike',
            ],
        ];
        $this->assertEquals($expected, $extractor->convert($item));
    }

    public function testLowerCasedFalsePreservesCase()
    {
        $extractor = new AkeneoOptionExtractor();
        $extractor->setOptions([
            'attribute_option_codes:list' => ['brand'],
            'reference_code' => false,
            'lower_cased' => false,
        ]);
        $item = ['brand' => 'Nike'];
        $expected = [
            'Nike-brand' => [
                'attribute' => 'brand',
                'code' => 'Nike',
                'reference' => 'Nike-brand',
                'original_value' => 'Nike',
            ],
        ];
        $this->assertEquals($expected, $extractor->convert($item));
    }

    public function testReferenceCodeAndLowerCasedFalse()
    {
        $extractor = new AkeneoOptionExtractor();
        $extractor->setOptions([
            'attribute_option_codes:list' => ['brand'],
            'reference_code' => true,
            'lower_cased' => false,
        ]);
        $item = ['brand' => 'Nike'];
        $expected = [
            'Nike-brand' => [
                'attribute' => 'brand',
                'code' => 'Nike',
                'reference' => 'Nike-brand',
                'original_value' => 'Nike',
            ],
        ];
        $this->assertEquals($expected, $extractor->convert($item));
    }

    public function testReferenceCodePatternWithSpecialCharacters()
    {
        $extractor = new AkeneoOptionExtractor();
        $extractor->setOptions([
            'attribute_option_codes:list' => ['brand'],
            'reference_code' => true,
            'reference_code_pattern' => 'new_pattern',
            'lower_cased' => true,
        ]);
        $item = ['brand' => 'NIke@2024'];
        $expected = [
            'nike_2024-brand' => [
                'attribute' => 'brand',
                'code' => 'nike_2024',
                'reference' => 'nike_2024-brand',
                'original_value' => 'NIke@2024',
            ],
        ];
        $this->assertEquals($expected, $extractor->convert($item));
    }

    public function testStringWithSeparatorExploded()
    {
        $extractor = new AkeneoOptionExtractor();
        $extractor->setOptions([
            'attribute_option_codes:list' => ['color'],
            'has_string_separator' => true,
            'separator' => ',',
        ]);
        $item = ['color' => 'Red,Blue'];
        $expected = [
            'red-color' => [
                'attribute' => 'color',
                'code' => 'red',
                'reference' => 'red-color',
                'original_value' => 'Red',
            ],
            'blue-color' => [
                'attribute' => 'color',
                'code' => 'blue',
                'reference' => 'blue-color',
                'original_value' => 'Blue',
            ],
        ];
        $this->assertEquals($expected, $extractor->convert($item));
    }

    public function testEmptyAndNullValuesAreIgnored()
    {
        $extractor = new AkeneoOptionExtractor();
        $extractor->setOptions([
            'attribute_option_codes:list' => ['color', 'brand'],
        ]);
        $item = ['color' => '', 'brand' => null];
        $expected = [];
        $this->assertEquals($expected, $extractor->convert($item));
    }

    public function testNonListedAttributesAreIgnored()
    {
        $extractor = new AkeneoOptionExtractor();
        $extractor->setOptions([
            'attribute_option_codes:list' => ['color'],
        ]);
        $item = ['color' => 'Red', 'size' => 'L'];
        $expected = [
            'red-color' => [
                'attribute' => 'color',
                'code' => 'red',
                'reference' => 'red-color',
                'original_value' => 'Red',
            ],
        ];
        $this->assertEquals($expected, $extractor->convert($item));
    }

    public function testMultipleOptionsWithDifferentPatterns()
    {
        $extractor = new AkeneoOptionExtractor();
        $extractor->setOptions([
            'attribute_option_codes:list' => ['color', 'brand'],
            'reference_code' => true,
            'reference_code_pattern' => 'new_pattern',
            'lower_cased' => true,
        ]);
        $item = ['color' => ['Red', 'Blue'], 'brand' => 'Nike'];
        $expected = [
            'red-color' => [
                'attribute' => 'color',
                'code' => 'red',
                'reference' => 'red-color',
                'original_value' => 'Red',
            ],
            'blue-color' => [
                'attribute' => 'color',
                'code' => 'blue',
                'reference' => 'blue-color',
                'original_value' => 'Blue',
            ],
            'nike-brand' => [
                'attribute' => 'brand',
                'code' => 'nike',
                'reference' => 'nike-brand',
                'original_value' => 'Nike',
            ],
        ];
        $this->assertEquals($expected, $extractor->convert($item));
    }

    public function testReferenceCodePatternWithFormatting()
    {
        $extractor = new AkeneoOptionExtractor();
        $extractor->setOptions([
            'attribute_option_codes:list' => ['brand'],
            'reference_code' => true,
            'reference_code_pattern' => 'new_pattern',
            'lower_cased' => true,
        ]);
        $item = ['brand' => 'Nike'];
        $expected = [
            'nike-brand' => [
                'attribute' => 'brand',
                'code' => 'nike',
                'reference' => 'nike-brand',
                'original_value' => 'Nike',
            ],
        ];
        $this->assertEquals($expected, $extractor->convert($item));
    }
}
