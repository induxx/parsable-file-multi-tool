<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\CopyAction;
use Misery\Component\Action\ValueFormatterAction;
use Misery\Component\Configurator\Configuration;
use Misery\Component\Reader\ItemCollection;
use Misery\Component\Source\Source;
use Misery\Component\Source\SourceCollection;
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
            'section' => 'values',
            'context' => [
                'pim_catalog_boolean' => [
                    'label' => [
                        'Y' => 'TRUE',
                        'N' => 'FALSE',
                    ],
                ],
            ],
            'format_key' => null,
            'filter_list' => [
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
                'length_cable' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => [
                            'unit' => 'METER',
                            'amount' => 1.0000,
                        ]
                    ]
                ],
            ],
            'sku' => '1234',
        ];

        $format->setOptions([
            'fields' => ['length_cable'],
            'section' => 'values',
            'context' => [
                'pim_catalog_metric' => [
                    'format' => '%amount% %unit%',
                ],
            ],
            'format_key' => null,
            'filter_list' => [
                'length_cable' => 'pim_catalog_metric',
            ],
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'description' => 'LV',
            'values' => [
                'length_cable' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => '1 METER',
                    ]
                ],
            ],
            'sku' => '1234',
        ], $format->apply($item));
    }

    public function test_it_should_value_format_a_simple_select(): void
    {
        $collection = new SourceCollection('internal');
        $source = Source::createSimple(
            new ItemCollection([
                [
                    'attribute' => 'brand',
                    'code' => 'nike',
                    'labels' => [
                        'nl_BE' => 'Nike nl_be',
                        'fr_BE' => 'Nike fr_be',
                        'de_DE' => 'Nike de_de',
                        'en_US' => 'Nike en_us',
                    ],
                ],
                [
                    'attribute' => 'brand',
                    'code' => 'louis',
                    'labels' => [
                        'nl_BE' => 'Louis Vuitton nl_be',
                        'fr_BE' => 'Louis Vuitton fr_be',
                        'de_DE' => 'Louis Vuitton de_de',
                        'en_US' => 'Louis Vuitton en_us',
                    ],
                ],
            ]),
            'attribute_options',
        );
        $collection->add($source);

        $format = new ValueFormatterAction();
        $format->setConfiguration($configuration = new Configuration());
        $configuration->addSources($collection);

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'values' => [
                'brand' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'louis',
                    ]
                ],
            ],
            'sku' => '1234',
        ];

        $format->setOptions([
            'fields' => ['brand'],
            'section' => 'values',
            'context' => [
                'pim_catalog_simpleselect' => [
                    'source' => 'attribute_options',
                    'filter' => [
                        'code' => '{value}',
                    ],
                    'return' => 'labels-nl_BE',
                ],
            ],
            'format_key' => null,
            'filter_list' => [
                'brand' => 'pim_catalog_simpleselect',
            ],
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'description' => 'LV',
            'values' => [
                'brand' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'Louis Vuitton nl_be',
                    ]
                ],
            ],
            'sku' => '1234',
        ], $format->apply($item));
    }
}