<?php

namespace Misery\Component\Converter\Akeneo\Csv;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Converter\AkeneoCsvHeaderContext;
use Misery\Component\Converter\ConverterInterface;
use Misery\Component\Converter\Matcher;

class Product implements ConverterInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private $csvHeaderContext;
    private $options = [
        'attribute_types:list' => null,
        'none_value_keys' => [
            'sku',
            'enabled',
            'family',
            'categories',
            'parent'
        ]
    ];

    public function __construct(AkeneoCsvHeaderContext $csvHeaderContext)
    {
        $this->csvHeaderContext = $csvHeaderContext;
    }

    public function convert(array $item): array
    {
        $codes = $this->getOption('attribute_types:list');
        $keyCodes = is_array($codes) ? array_keys($codes): null;
        $separator = '-';
        $output = [];

        foreach ($item as $key => $value) {
            $keys = explode($separator, $key);
            $masterKey = $keys[0];

            if (in_array($masterKey, $this->getOption('none_value_keys'))) {
                continue;
            }

            if ($keyCodes && false === in_array($masterKey, $keyCodes)) {
                continue;
            }

            if (str_ends_with($key, '-unit') !== false) {
                unset($item[$key]);
                continue;
            }

            # values
            $prep = $this->csvHeaderContext->create($item)[$key];
            $prep['data'] = $value;

            # metrics
            if ($codes[$masterKey] === 'pim_catalog_metric') {
                $prep['data'] = [
                    'amount' => $value,
                    'unit' => $item[str_replace($masterKey, $masterKey.'-unit', $key)] ?? null,
                ];
            }
            # multiselect
            if ($codes[$masterKey] === 'pim_catalog_multiselect') {
                $prep['data'] = array_filter(explode(',', $prep['data']));
            }

            $matcher = Matcher::create('values|'.$masterKey, $prep['locale'], $prep['scope']);
            unset($prep['key']); // old way of storing the original key
            $output[$k = $matcher->getMainKey()] = $prep;
            $output[$k]['matcher'] = $matcher;

            unset($item[$key]);
        }

        return $item+$output;
    }

    public function revert(array $item): array
    {
        return $item;
    }

    public function getName(): string
    {
        return 'akeneo/product/csv';
    }
}