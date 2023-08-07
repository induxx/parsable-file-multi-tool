<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\CopyAction;
use Misery\Component\Action\ValueFormatterAction;
use PHPUnit\Framework\TestCase;

class ValueFormatterActionTest extends TestCase
{
    public function test_it_should_value_format_a_boolean(): void
    {
        $format = new ValueFormatterAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'values' => [
                'enabled' => '1',
            ],
            'sku' => '1234',
        ];

        $format->setOptions([
            'fields' => ['enabled'],
            'context' => [
                'pim_catalog_boolean' => [
                    'label' => [
                        'Y' => 'TRUE',
                        'N' => 'FALSE',
                    ],
                ],
            ],
            'format_key' => null,
            'list' => [
                'enabled' => 'pim_catalog_boolean',
            ],
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'description' => 'LV',
            'values' => [
                'enabled' => 'TRUE',
            ],
            'sku' => '1234',
        ], $format->apply($item));
    }

    public function test_it_should_value_format_a_metric(): void
    {
        $format = new ValueFormatterAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'values' => [
                'enabled' => '1',
            ],
            'sku' => '1234',
        ];

        $format->setOptions([
            'fields' => ['enabled'],
            'context' => [
                'pim_catalog_boolean' => [
                    'label' => [
                        'Y' => 'TRUE',
                        'N' => 'FALSE',
                    ],
                ],
            ],
            'format_key' => null,
            'list' => [
                'enabled' => 'pim_catalog_boolean',
            ],
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'description' => 'LV',
            'values' => [
                'enabled' => 'TRUE',
            ],
            'sku' => '1234',
        ], $format->apply($item));
    }
}