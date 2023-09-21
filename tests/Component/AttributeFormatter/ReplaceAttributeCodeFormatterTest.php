<?php

namespace Component\AttributeFormatter;

use Misery\Component\AttributeFormatter\ReplaceAttributeCodeFormatter;
use Misery\Component\Reader\ItemCollection;
use Misery\Component\Source\Source;
use PHPUnit\Framework\TestCase;

class ReplaceAttributeCodeFormatterTest extends TestCase
{
    public function dataCollection()
    {
        return new ItemCollection([
            [
                'code' => 'red',
                'labels-nl_BE' => 'Rood',
            ]
        ]);
    }

//    public function dataProviderForFormat()
//    {
//        return [
//            // Test case 1: String input
//            [
//                'value' => 'Hello {value}',
//                'context' => [
//                    'separator' => '-',
//                    'current-attribute-code' => 'attribute_code',
//                ],
//                'expectedResult' => 'Hello attribute_code',
//            ],
//            // Test case 2: Array input
//            [
//                'value' => ['Item 1: {value}', 'Item 2: {value}'],
//                'context' => [
//                    'separator' => '-',
//                    'current-attribute-code' => 'attribute_code',
//                ],
//                'expectedResult' => ['Item 1: attribute_code', 'Item 2: attribute_code'],
//            ],
//            // Add more test cases here as needed
//        ];
//    }

    public function testFormatWithStringValue()
    {
        $formatter = new ReplaceAttributeCodeFormatter(
            Source::createSimple($this->dataCollection(), 'test')
        );

        $context = [
            'source' => 'test',
            'filter' => [
                'attribute' => '{attribute-code}',
                'code' => '{value}'
            ],
            'return' => 'labels-nl_BE',
          //  'separator' => '-',
          //  'current-attribute-code' => 'attribute_code',
        ];

        $value = 'red';
        $expectedResult = 'Rood';

        $formattedValue = $formatter->format($value, $context);

        $this->assertEquals($expectedResult, $formattedValue);
    }
//
//    public function testFormatWithArrayValue()
//    {
//        $source = new Source(); // You should create an instance of your Source class here
//        $formatter = new ReplaceAttributeCodeFormatter($source);
//
//        $context = [
//            'separator' => '-',
//            'current-attribute-code' => 'attribute_code',
//        ];
//
//        $value = ['Item 1: {value}', 'Item 2: {value}'];
//        $expectedResult = ['Item 1: attribute_code', 'Item 2: attribute_code'];
//
//        $formattedValue = $formatter->format($value, $context);
//
//        $this->assertEquals($expectedResult, $formattedValue);
//    }
//
//    public function testFormatWithFilter()
//    {
//        $source = new Source(); // You should create an instance of your Source class here
//        $formatter = new ReplaceAttributeCodeFormatter($source);
//
//        $context = [
//            'separator' => '-',
//            'current-attribute-code' => 'attribute_code',
//            'filter' => ['some_filter'],
//            'return' => 'some_field',
//        ];
//
//        // Define your expected result based on your source data and filter
//        $expectedResult = 'Expected Result';
//
//        $formattedValue = $formatter->format('Input Value', $context);
//
//        $this->assertEquals($expectedResult, $formattedValue);
//    }
}
