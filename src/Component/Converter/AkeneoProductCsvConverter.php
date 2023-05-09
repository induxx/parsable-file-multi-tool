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
    private $header;
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
        if (null === $this->header) {
            // header Factory
            $this->header = (new AS400ArticleAttributesHeaderContext())->create(
                $this->getOption('attribute_types:list'),
                $this->getOption('localizable_attribute_codes:list'),
                $this->getOption('scopable_attribute_codes:list'),
                $this->getOption('locales')
            );
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
                    $output[$this->header->createItemHeader($matcher->getPrimaryKey(), ['extra' => 'unit'])] = $itemValue['data']['unit'];
                }
            }
        }
        $output = array_replace($this->header->getHeaders(), $output);

        unset($item['values']);
        $item = $this->decoder->decode($item);
        $associations = $item['associations'];
        unset($item['associations']);

        return $item+$output+$associations;
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