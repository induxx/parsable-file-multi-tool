<?php

namespace Tests\Misery\Component\Converter\Akeneo\Api;

use Misery\Component\Converter\Akeneo\Api\CatalogLabelConverter;
use PHPUnit\Framework\TestCase;

class CatalogLabelConverterTest extends TestCase
{
    public function testConvertWithLabels()
    {
        $input = [
            'code' => 'foo',
            'labels' => [
                'en_US' => 'Foo',
                'nl_NL' => 'Foe',
            ],
        ];
        $expected = [
            'code' => 'foo',
            'label-en_US' => 'Foo',
            'label-nl_NL' => 'Foe',
        ];
        $this->assertEquals($expected, CatalogLabelConverter::convert($input));
    }

    public function testConvertWithoutLabels()
    {
        $input = [
            'code' => 'bar',
        ];
        $expected = [
            'code' => 'bar',
        ];
        $this->assertEquals($expected, CatalogLabelConverter::convert($input));
    }

    public function testRevertWithLabelLocales()
    {
        $input = [
            'code' => 'foo',
            'label-en_US' => 'Foo',
            'label-nl_NL' => 'Foe',
        ];
        $expected = [
            'code' => 'foo',
            'labels' => [
                'en_US' => 'Foo',
                'nl_NL' => 'Foe',
            ],
        ];
        $this->assertEquals($expected, CatalogLabelConverter::revert($input));
    }

    public function testRevertWithoutLabelLocales()
    {
        $input = [
            'code' => 'bar',
        ];
        $expected = [
            'code' => 'bar',
        ];
        $this->assertEquals($expected, CatalogLabelConverter::revert($input));
    }

    public function testConvertWithEmptyLabels()
    {
        $input = [
            'code' => 'baz',
            'labels' => [],
        ];
        $expected = [
            'code' => 'baz',
        ];
        $this->assertEquals($expected, CatalogLabelConverter::convert($input));
    }
}
