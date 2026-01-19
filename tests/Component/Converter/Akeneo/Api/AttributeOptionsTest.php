<?php

namespace Tests\Misery\Component\Converter\Akeneo\Api;

use Misery\Component\Converter\Akeneo\Api\AttributeOptions;
use PHPUnit\Framework\TestCase;

class AttributeOptionsTest extends TestCase
{
    public function testConvertUsesCatalogLabels()
    {
        $converter = new AttributeOptions();
        $input = [
            'code' => 'option_code',
            'labels' => [
                'en_US' => 'Option',
                'nl_NL' => 'Optie',
            ],
        ];

        $expected = [
            'code' => 'option_code',
            'label-en_US' => 'Option',
            'label-nl_NL' => 'Optie',
        ];

        $this->assertEquals($expected, $converter->convert($input));
    }

    public function testRevertCastsSortOrder()
    {
        $converter = new AttributeOptions();
        $input = [
            'code' => 'option_code',
            'label-en_US' => 'Option',
            'sort_order' => '10',
        ];

        $expected = [
            'code' => 'option_code',
            'labels' => [
                'en_US' => 'Option',
            ],
            'sort_order' => 10,
        ];

        $this->assertEquals($expected, $converter->revert($input));
    }

    public function testName()
    {
        $converter = new AttributeOptions();
        $this->assertEquals('akeneo/options/api', $converter->getName());
    }
}
