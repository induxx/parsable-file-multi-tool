<?php

namespace Misery\Component\Converter;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Common\Registry\Registry;
use Misery\Component\Decoder\ItemDecoder;
use Misery\Component\Decoder\ItemDecoderFactory;
use Misery\Component\Format\ArrayListFormat;
use Misery\Component\Format\StringToIntFormat;
use Misery\Component\Format\StringToListFormat;
use Misery\Component\Format\StringToMultiListFormat;

class AkeneoProductCsvConverter implements ConverterInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private $options = [
        'attribute_types:list' => [],
        'localizable_attribute_codes:list' => [],
        'scopable_attribute_codes:list' => [],
        'locales' => [],
        'properties' => [
            'categories' => [
                'list' => [],
            ],
            'groups' => [
                'list' => [],
            ],
            'associations' => [
                'multi-list' => [],
            ],
            'quantified_associations' => [
                'list' => [],
            ]
        ]
    ];
    private $decoder;

    public function convert(array $item): array
    {
        $tmp = [];
        // first we need to convert the values
        foreach ($item['values'] ?? [] as $key => $valueSet) {
            foreach ($valueSet ?? [] as  $value) {
                $matcher = Matcher::create('values|'.$key, $value['locale'], $value['scope']);
                $tmp[$key = $matcher->getMainKey()] = $value;
                $tmp[$key]['matcher'] = $matcher;
            }
        }
        unset($item['values']);

        return $item+$tmp;
    }

    public function revert(array $item): array
    {
        if (null === $this->decoder) {
            $this->decoder = $this->ItemDecoderFactory()->createItemDecoder([
                'encode' => $this->getOption('properties'),
            ]);
        }

        $output = [];
        foreach ($item ?? [] as $key => $itemValue) {
            $matcher = $itemValue['matcher'] ?? null;
            /** @var $matcher Matcher */
            if ($matcher && $matcher->matches('values')) {
                unset($itemValue['matcher']);
                unset($item[$key]);
                if (isset($itemValue['data']['unit'])) {
                    $output[$matcher->getRowKey()] = $itemValue['data']['amount'];
                    $output[$matcher->getRowKey().'-unit'] = $itemValue['data']['unit'];
                }
                if (is_string($itemValue['data'])) {
                    $output[$matcher->getRowKey()] = $itemValue['data'];
                }
            }
        }

        // correct the identifier
        $item['sku'] = $item['identifier'];
        unset($item['identifier']);

        unset($item['values']);
        $item = $this->decoder->decode($item);

        // correct the associations
        if (isset($item['associations'])) {
            $associations = $item['associations'];
            unset($item['associations']);
            $item+=$associations;
        }

        return $item+$output;
    }

    private function ItemDecoderFactory(): ItemDecoderFactory
    {
        $encoderFactory = new ItemDecoderFactory();

        $formatRegistry = new Registry('format');
        $formatRegistry
            ->register(StringToListFormat::NAME, new StringToListFormat())
            ->register(StringToIntFormat::NAME, new StringToIntFormat())
            ->register(ArrayListFormat::NAME, new ArrayListFormat())
        ;
        $encoderFactory->addRegistry($formatRegistry);

        return $encoderFactory;
    }

    public function getName(): string
    {
        return 'akeneo/product/csv';
    }
}