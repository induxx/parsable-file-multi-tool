<?php

namespace Misery\Component\Converter\Akeneo\Csv;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Common\Registry\Registry;
use Misery\Component\Converter\AkeneoCsvHeaderContext;
use Misery\Component\Converter\ConverterInterface;
use Misery\Component\Converter\Matcher;
use Misery\Component\Decoder\ItemDecoder;
use Misery\Component\Decoder\ItemDecoderFactory;
use Misery\Component\Encoder\ItemEncoder;
use Misery\Component\Encoder\ItemEncoderFactory;
use Misery\Component\Format\StringToBooleanFormat;
use Misery\Component\Format\StringToListFormat;

class Product implements ConverterInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private AkeneoCsvHeaderContext $csvHeaderContext;
    private ItemEncoder $encoder;
    private ItemDecoder $decoder;

    private $options = [
        'investigate' => true, # if true, will not convert, but return the header
        'container' => 'values',
        'default_currency' => 'EUR',
        'single_currency' => true,
        'attribute_types:list' => null, # this key value list is optional, improves type matching for options, metrics, prices
        'identifier' => 'sku',
        'associations' => ['RELATED'],
        'associations:keys' => [],
        'quantified_associations' => [],
        'quantified_associations:keys' => [],
        'properties' => [
            'sku' => [
                'text' => null,
            ],
            'enabled' => [
                'boolean' => null,
            ],
            'family' => [
                'text' => null,
            ],
            'categories'=> [
                'list' => null,
            ],
            'parent' => [
                'text' => null,
            ],
        ],
        'parse' => [],
    ];

    public function __construct(AkeneoCsvHeaderContext $csvHeaderContext)
    {
        $this->csvHeaderContext = $csvHeaderContext;
        list($encoder, $decoder) = $this->ItemEncoderDecoderFactory();
        $this->encoder = $encoder->createItemEncoder([
            'encode' => $this->getOption('properties'),
            'parse' => $this->getOption('parse'),
        ]);
        $this->decoder = $decoder->createItemDecoder([
            'decode' => $this->getOption('properties'),
            'parse' => $this->getOption('parse'),
        ]);
    }

    private function ItemEncoderDecoderFactory(): array
    {
        $encoderFactory = new ItemEncoderFactory();
        $decoderFactory = new ItemDecoderFactory();

        $formatRegistry = new Registry('format');
        $formatRegistry
            ->register(StringToListFormat::NAME, new StringToListFormat())
            ->register(StringToBooleanFormat::NAME, new StringToBooleanFormat())
        ;

        $encoderFactory->addRegistry($formatRegistry);
        $decoderFactory->addRegistry($formatRegistry);

        return [$encoderFactory, $decoderFactory];
    }

    private function findAssociationKeys(array $item): void
    {
        $associationKeys = [];
        foreach ($item as $key => $value) {
            if (str_ends_with($key, '-quantity')) {
                continue; // skip quantified associations
            }
            if (str_contains($key, '-')) {
                $keys = explode('-', $key);
                if (count($keys) > 1 && in_array($keys[0], $this->getOption('associations'))) {
                    $associationKeys[] = $key;
                }
            }
        }

        $this->setOption('associations:keys', array_unique($associationKeys));
    }

    private function findQuantifiedAssociationKeys(array $item): void
    {
        $quantifiedKeys = [];
        $suffix = '-quantity';
        $suffixLen = strlen($suffix);

        foreach ($item as $key => $value) {
            if (substr_compare($key, $suffix, -$suffixLen) === 0) {
                $code = explode('-', $key)[0];
                $type =  explode('-', $key)[1];
                $quantifiedKeys[$type][] = [
                    'code' => $code,
                    'quantity' => $key,
                    'id' => substr($key, 0, -$suffixLen),
                ];
            }
        }

        $this->setOption('quantified_associations:keys', $quantifiedKeys);
    }

    private function convertQuantifiedAssociations(array &$item, array &$output): void
    {
        $quantifiedAssociations = [];
        foreach ($this->getOption('quantified_associations:keys') as $assocType => $assocs) {
            foreach ($assocs as $assoc) {
                $ids = isset($item[$assoc['id']]) ? explode(',', $item[$assoc['id']]) : [];
                $quantities = isset($item[$assoc['quantity']]) ? explode(',', $item[$assoc['quantity']]) : [];
                $products = [];
                foreach ($ids as $i => $identifier) {
                    $identifier = trim($identifier);
                    if ($identifier === '') {
                        continue;
                    }
                    $quantity = isset($quantities[$i]) ? (int) $quantities[$i] : null;
                    $products[] = [
                        'identifier' => $identifier,
                        'quantity' => $quantity,
                    ];
                }
                if (!empty($products)) {
                    if (!isset($quantifiedAssociations[$assoc['code']])) {
                        $quantifiedAssociations[$assoc['code']] = [];
                    }
                    $quantifiedAssociations[$assoc['code']][$assocType] = $products;
                }
                // Optionally unset processed keys from $item
                unset($item[$assoc['id']], $item[$assoc['quantity']]);
            }
        }
        if (!empty($quantifiedAssociations)) {
            $output['quantified_associations'] = $quantifiedAssociations;
        }
    }

    private function convertAssociations(array &$item, array &$output): void
    {
        $associations = [];
        foreach ($this->getOption('associations:keys') as $key) {
            $keys = explode('-', $key);
            $assocType = $keys[0];
            $assocCode = $keys[1];
            if (!empty($item[$key])) {
                if (!isset($associations[$assocType])) {
                    $associations[$assocType] = [];
                }
                $associations[$assocType][$assocCode] = array_filter(explode(',', $item[$key]));
            }
            unset($item[$key]); // Optionally unset processed keys from $item
        }
        if (!empty($associations)) {
            $output['associations'] = $associations;
        }
    }

    public function convert(array $item): array
    {
        $this->csvHeaderContext->unsetHeader();
        $identifier = $this->getOption('identifier');
        $codes = $this->getOption('attribute_types:list');
        $keyCodes = is_array($codes) ? array_keys($codes): null;
        $separator = '-';
        $output = [];
        $prices = [];

        if ($this->getOption('investigate')) {
            $this->findAssociationKeys($item);
            $this->findQuantifiedAssociationKeys($item);
            $this->setOption('investigate', false);
        }

        $item = $this->encoder->encode($item);

        if ([] !== $this->getOption('quantified_associations:keys')) {
            $this->convertQuantifiedAssociations($item, $output);
        }
        if ([] !== $this->getOption('associations:keys')) {
            $this->convertAssociations($item, $output);
        }

        foreach ($item as $key => $value) {
            $keys = explode($separator, $key);
            $masterKey = $keys[0];

            if (in_array($masterKey, $this->getOption('associations'))) {
                $output['associations'][$masterKey][$keys[1]] = array_filter(explode(',', $value));
                unset($item[$key]);
                continue;
            }

            if (in_array($masterKey, array_keys($this->getOption('properties')))) {
                continue;
            }

            if ($keyCodes && false === in_array($masterKey, $keyCodes)) {
                continue;
            }

            if ($identifier === $key) {
                continue;
            }

            if (str_ends_with($key, '-unit') !== false) {
                unset($item[$key]);
                continue;
            }

            # values
            $prep = $this->csvHeaderContext->create($item)[$key];
            $prep['data'] = $value;

            if (is_array($codes)) {
                # metrics
                if ($codes[$masterKey] === 'pim_catalog_metric') {
                    $prep['data'] = [
                        'amount' => $value,
                        'unit' => $item[str_replace($masterKey, $masterKey.'-unit', $key)] ?? null,
                    ];
                }
                # reference_data_multiselect
                if ($codes[$masterKey] === 'pim_reference_data_multiselect') {
                    $prep['data'] = str_replace('-', '_',  $prep['data']);
                    $prep['data'] = array_filter(explode(',', $prep['data']));
                }
                if ($codes[$masterKey] === 'pim_catalog_simpleselect') {
                    $prep['data'] = str_replace('-', '_',  $prep['data']);
                }
                if ($codes[$masterKey] === 'pim_reference_data_simpleselect') {
                    $prep['data'] = str_replace('-', '_',  $prep['data']);
                }
                # multiselect
                if ($codes[$masterKey] === 'pim_catalog_multiselect') {
                    $prep['data'] = str_replace('-', '_',  $prep['data']);
                    $prep['data'] = array_filter(explode(',', $prep['data']));
                }
                # pim_catalog_price_collection | single default currency EUR | will work in most cases
                if ($codes[$masterKey] === 'pim_catalog_price_collection' && true === $this->getOption('single_currency')) {
                    $prep['data'] = [['amount' => $prep['data'], 'currency' => $this->getOption('default_currency')]];
                } elseif ($codes[$masterKey] === 'pim_catalog_price_collection') {
                    $currency = $prep['scope'];
                    $prep['scope'] = null;
                    $prices[$masterKey][] = ['amount' => $prep['data'], 'currency' => $currency];
                    $prep['data'] = $prices[$masterKey];
                }
                # boolean expecting CSV values
                if ($codes[$masterKey] === 'pim_catalog_boolean') {
                    if ($prep['data'] === '0') {
                        $prep['data'] = false;
                    }
                    if ($prep['data'] === '1') {
                        $prep['data'] = true;
                    }
                    if ($prep['data'] === '') {
                        $prep['data'] = null;
                    }
                }
                # number
                if ($codes[$masterKey] === 'pim_catalog_number') {
                    $prep['data'] = (is_string($prep['data'])) ? $this->numberize($prep['data']): $prep['data'];
                }
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
        $container = $this->getOption('container');
        $identifier = $this->getOption('identifier');
        $codes = $this->getOption('attribute_types:list');
        $keyCodes = is_array($codes) ? array_keys($codes): null;

        $output = [];
        # first isolate the properties
        $output[$identifier] = $item[$identifier] ?? $item['identifier'] ?? null;
        if (isset($item['enabled'])) {
            $output['enabled'] = $item['enabled'];
        }
        if (array_key_exists('family', $item)) {
            $output['family'] = $item['family'];
        }
        if (array_key_exists('family_variant', $item)) {
            $output['family_variant'] = $item['family_variant'];
        }
        if (array_key_exists('categories', $item)) {
            $output['categories'] = $item['categories'];
        }
        if (array_key_exists('parent', $item)) {
            $output['parent'] = $item['parent'];
        }
        $output = $this->decoder->decode($output);

        # now iterate over the item and match the remaining values
        foreach ($item as $key => $itemValue) {
            if ($keyCodes && false === in_array($key, $keyCodes)) {
                continue;
            }

            $matcher = $itemValue['matcher'] ?? null;
            /** @var $matcher Matcher */
            if ($matcher && $matcher->matches($container)) {
                unset($itemValue['matcher']);
                unset($item[$key]);
                if (is_array($itemValue['data']) && array_key_exists('unit', $itemValue['data'])) {
                    $output[$matcher->getRowKey()] = (string) $itemValue['data']['amount'];
                    $output[$matcher->getRowKey().'-unit'] = $itemValue['data']['unit'];
                    continue;
                }
                if (is_array($itemValue['data']) && isset($itemValue['data'][0]['amount'])) {
                    // CSV can accept 2 column types, price and price-EUR
                    // for safety we should restore the old key here
                    $output[$matcher->getRowKey().'-'.$itemValue['data'][0]['currency']] = $itemValue['data'][0]['amount'];
                    continue;
                }
                if (is_array($itemValue['data'])) {
                    $output[$matcher->getRowKey()] = implode(',', $itemValue['data']);
                    continue;
                }
                if (is_bool($itemValue['data'])) {
                    $output[$matcher->getRowKey()] = $itemValue['data'] ? '1' : '0';
                    continue;
                }
                if (array_key_exists('data', $itemValue)) {
                    $output[$matcher->getRowKey()] = $itemValue['data'];
                }
            }
        }

        return $output;
    }

    /**
     * COPY/PASTA \Misery\Component\Akeneo\AkeneoTypeBasedDataConverter
     */
    private function numberize($value)
    {
        if (is_integer($value)) {
            return $value;
        }
        if (is_float($value)) {
            return $value;
        }
        if (is_string($value)) {
            $posNum = str_replace(',', '.', $value);
            return is_numeric($posNum) ? $posNum: $value;
        }
    }

    public function getName(): string
    {
        return 'akeneo/product/csv';
    }
}