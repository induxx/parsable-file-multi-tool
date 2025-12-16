<?php

namespace Misery\Component\Converter;

use Misery\Component\Akeneo\Header\AkeneoHeaderTypes;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Configurator\ConfigurationAwareInterface;
use Misery\Component\Configurator\ConfigurationTrait;
use Misery\Component\Modifier\ReferenceCodeModifier;
use Misery\Component\Source\Source;

/**
 * This Converter converts flat product to std data
 * We focus on correcting with minimal issues
 * The better the input you give the better the output
 */
class AkeneoFlatProductToCsvConverter implements ConverterInterface, ConfigurationAwareInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;
    use ConfigurationTrait;

    private $options = [
        'attributes:list' => [],
        'attribute_types:list' => [],
        'localizable_attribute_codes:list' => [],
        'scopable_attribute_codes:list' => [],
        'default_metrics:list' => [],
        'attribute_options:source' => null, # SourceInterface
        'value_separators' => [','],
        'set_default_metrics' => false,
        'filter_option_codes' => true,
        'reference_code' => false, # LEGACY FALSE forces the option code to be a reference-able code
        'reference_code_pattern' => 'old_pattern', # LEGACY FALSE forces the option code to be a reference-able code
        'lower_cased' => false, # LEGACY FALSE forces the option code to be lower cased
        'default_locale' => null,
        'default_scope' => null,
        'default_currency' => null,
        'container' => 'values',
        'option_label' => 'label-nl_BE',
        'properties' => ['sku', 'family', 'parent', 'enabled', 'categories', 'associations', 'groups', 'quantified_associations'],
        'quantified_associations' => [],
        'associations' => [],
        'boolean_mapping' => [
            'true_values' => [ 'true','1' ],
            'false_values' => ['false', '0' ],
        ],
    ];

    private $decoder;

    public function convert(array $item): array
    {
        if (is_string($this->getOption('attribute_options:source'))) {
            $this->setOption(
                'attribute_options:source',
                $this->configuration->getSources()->get($this->getOption('attribute_options:source'))
            );
        }
        $tmp = [];
        $container = $this->getOption('container');
        // first we need to convert the values
        foreach ($item[$container] ?? $item as $key => $value) {

            list($masterKey, $context) = $this->createScopes($key);
            if (in_array($masterKey, ['sku', 'family', 'parent'])) {
                continue;
            }
            if ($masterKey === 'categories' && is_string($item[$masterKey])) {
                $item[$masterKey] = explode(',', $item[$masterKey]);
                continue;
            }

            $context['item'] = $item; # pass the item to the context, for [metrics, price-collections, etc]

            $value = $this->getAkeneoDataStructure($masterKey, $value, $context);
            if (is_array($value) && array_key_exists('data', $value)) {
                $matcher = Matcher::create($container.'|'.$masterKey, $value['locale'], $value['scope']);
                $k = $matcher->getMainKey();
                if (isset($tmp[$k])) {
                    unset($item[$key]);
                    continue; # don't let -unit overwrite the main key
                }
                $tmp[$k] = $value;
                $tmp[$k]['matcher'] = $matcher;
                unset($item[$key]);
            }
        }
        unset($item[$container]);

        return $item+$tmp;
    }

    /**
     * short_description-en_US-print
     *
     * @internal Doesn't work with metrics + price-collections, you need type context
     *
     * becomes
     * masterKey short_description
     * context [
     *   locale => en_US,
     *   scope => print,
     */
    private function createScopes(string $key, $separator = '-'): array
    {
        $explodedKeys = explode($separator, $key);
        $masterKey = array_shift($explodedKeys);

        $isLocalizable = in_array(
            $masterKey,
            $this->getOption('localizable_attribute_codes:list') ?? [],
            true
        );
        $isScopable = in_array(
            $masterKey,
            $this->getOption('scopable_attribute_codes:list') ?? [],
            true
        );

        // When attribute metadata is available we can accurately map locale/scope.
        if ($isLocalizable && $isScopable) {
            return [$masterKey, [
                'locale' => $explodedKeys[0] ?? null,
                'scope' => $explodedKeys[1] ?? null,
            ]];
        }

        if ($isLocalizable) {
            return [$masterKey, [
                'locale' => $explodedKeys[0] ?? null,
                'scope' => null,
            ]];
        }

        if ($isScopable) {
            return [$masterKey, [
                'locale' => null,
                'scope' => $explodedKeys[0] ?? null,
            ]];
        }

        // Legacy fallback: assume locale first, scope second when no metadata provided.
        return [$masterKey, [
            'locale' => $explodedKeys[0] ?? null,
            'scope' => $explodedKeys[1] ?? null,
        ]];
    }

    public function getAkeneoDataStructure(string $attributeCode, $value, array $context)
    {
        $referenceCode = $this->getOption('reference_code');
        $lowerCased = $this->getOption('lower_cased');
        $valueSeparators = $this->getOption('value_separators');
        $item = $context['item'] ?? [];

        $type = $this->getOption('attribute_types:list')[$attributeCode] ?? null;
        if (null === $type) {
            return $value;
        }

        if (is_array($value)) {
            if (
                array_key_exists('locale', $value) &&
                array_key_exists('data', $value) &&
                array_key_exists('scope', $value)
            ) {
                return $value;
            }
        }

        // CSV empty string data is converted to null
        if ($value === '') {
            $value = null;
        }

        $localizable = in_array(
            $attributeCode,
            $this->getOption('localizable_attribute_codes:list')
        );
        $scopable = in_array(
            $attributeCode,
            $this->getOption('scopable_attribute_codes:list')
        );

        switch ($type) {
            case AkeneoHeaderTypes::TEXT:
                // no changes
                break;
            case AkeneoHeaderTypes::BOOLEAN:
                if (is_string($value)) {
                    $value = strtolower($value);
                    if (in_array($value, $this->getOption('boolean_mapping')['true_values'])) {
                        $value = true;
                    } elseif (in_array($value, $this->getOption('boolean_mapping')['false_values'])) {
                        $value = false;
                    }
                }
                break;
            case AkeneoHeaderTypes::NUMBER:
                if (!empty($value)) {
                    $value = $this->numberize($value);
                }
                break;
            case AkeneoHeaderTypes::SELECT:
                // TODO implement attributes reader
                if (is_string($value) && $this->getOption('filter_option_codes')) {
                    $value = $this->filterOptionCode($value, $attributeCode, $referenceCode, $lowerCased);
                }
                break;
            case AkeneoHeaderTypes::MULTISELECT:
                // TODO implement attributes reader
                if (empty($value)) {
                    $value = [];
                }

                // if the value is a comma separated string
                $value = $this->separateValue($value, $valueSeparators);

                if ($this->getOption('filter_option_codes')) {
                    $value = $this->filterOptionCodes($value, $attributeCode, $referenceCode, $lowerCased);
                }
                break;
            case AkeneoHeaderTypes::PRICE:
                $amount = null;
                $unit = $this->getOption('default_currency');
                if ($this->isNumberic($value)) {
                    $amount = $this->numberize($value);
                }
                if (is_array($value)) {
                    if (array_key_exists('amount', $value)) {
                        $amount = $value['amount'];
                    }
                    if (array_key_exists('currency', $value)) {
                        $unit = $value['currency'];
                    }
                }

                $value = [[
                    'amount' => $amount,
                    'currency' => $unit,
                ]];
                break;
            case AkeneoHeaderTypes::METRIC:
                $amount = null;
                $unit = $this->getOption('default_metrics:list')[$attributeCode] ?? null;
                if ($this->isNumberic($value)) {
                    $amount = $this->numberize($value);
                }
                if (isset($item[$attributeCode.'-unit'])) {
                    $unit = $item[$attributeCode.'-unit'];
                } elseif (is_array($value)) {
                    if (array_key_exists('amount', $value)) {
                        $amount = $value['amount'];
                    }
                    if (array_key_exists('unit', $value)) {
                        $unit = $value['unit'];
                    }
                }

                $value = [
                    'amount' => $amount,
                    'unit' => $unit,
                ];
                break;
            case AkeneoHeaderTypes::REFDATA_MULTISELECT:
                $value = $this->separateValue($value, $valueSeparators);
                if ($this->getOption('filter_option_codes')) {
                    $value = $this->filterOptionCodes($value, $attributeCode, $referenceCode, $lowerCased);
                }
                break;
            case AkeneoHeaderTypes::REFDATA_SELECT:
                // no changes
                if (is_string($value) && $this->getOption('filter_option_codes')) {
                    $value = $this->filterOptionCode($value, $attributeCode, $referenceCode, $lowerCased);
                }
                break;
        }

        return [
            'locale' => $localizable ? $context['locale'] ?? $this->getOption('default_locale') : null,
            'scope' => $scopable ? $context['scope'] ?? $this->getOption('default_scope') : null,
            'data' => $value,
        ];
    }

    private function separateValue($value, array $valueSeparators)
    {
        if (is_string($value)) {
            $seperated = false;
            foreach ($valueSeparators as $valueSeparator) {
                if (str_contains($value, $valueSeparator)) {
                    $value = explode($valueSeparator, $value);
                    $seperated = true;
                    break;
                }
            }
            if (!$seperated) {
                $value = [$value];
            }
        }

        return $value;
    }

    /**
     * Basic support for comma and dot decimals
     * 1,23 or 1.23 are numeric
     * - US format: 1,234.56
     * - EU format: 1.234,56
     */
    private function isNumberic($value): bool
    {
        if (!is_scalar($value) || is_bool($value) || $value === null) {
            return false;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return false;
        }
        $value = str_replace(' ', '', $value);

        // US format: 1,234.56
        if (preg_match('/^\d{1,3}(,\d{3})*\.\d+$/', $value)) {
            return is_numeric(str_replace(',', '', $value));
        }

        // EU format: 1.234,56
        if (preg_match('/^\d{1,3}(\.\d{3})*,\d+$/', $value)) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
            return is_numeric($value);
        }

        // Plain number, or simple decimal with dot or comma
        if (preg_match('/^-?\d+([.,]\d+)?$/', $value)) {
            return is_numeric(str_replace(',', '.', $value));
        }

        return false;
    }

    private function numberize($value)
    {
        if (is_float($value)) {
            return $value;
        }
        if (is_integer($value)) {
            return $value;
        }
        if (is_string($value)) {
            $posNum = str_replace(',', '.', $value);
            return is_numeric($posNum) ? $posNum: $value;
        }
    }

    private function filterOptionCode(string $value, string $attributeCode, bool $referenceCode = true, bool $lowerCase = true): string
    {
        $value = trim($value);
        if (is_object($this->getOption('attribute_options:source'))) {
            $nvalue = $this->findAttributeOptionCode($attributeCode, $value);
            if (null !== $nvalue) {
                return $nvalue;
            }
        }

        if ($referenceCode) {
            $mod = new ReferenceCodeModifier();
            $mod->setOption('use_pattern', $this->getOption('reference_code_pattern'));
            $value = $mod->modify($value);
        }
        if ($lowerCase) {
            $value = strtolower($value);
        }
        if (!$lowerCase && !$referenceCode) {
            $value = str_replace('-', '_', str_replace(' ', '-', $value));
        }

        return $value;
    }

    private function filterOptionCodes(array $optionValues, string $attributeCode, bool $referenceCode = true, bool $lowerCase = true): array
    {
        return array_map(function ($optionValue) use ($referenceCode, $lowerCase, $attributeCode) {
            return $this->filterOptionCode($optionValue['code'] ?? $optionValue, $attributeCode, $referenceCode, $lowerCase);
        }, $optionValues);
    }

    /**
     * This function return the option_code that was made earlier
     * When generating option codes we expect a full export strategy
     */
    public function findAttributeOptionCode(string $attributeCode, string $optionLabel)
    {
        /** @var Source $source */
        $source = $this->getOption('attribute_options:source');
        return $source->getCachedReader()->find([
            'attribute' => $attributeCode,
            'original_value' => $optionLabel]
        )->getIterator()->current()['code'] ?? null;
    }

    /**
     * This function will extract attribute values from the item based on the attribute:list
     */
    public function getProductValues(array $item): \Generator
    {
        foreach ($this->getOption('attributes:list') ?? [] as $attributeCode) {
            if (array_key_exists($attributeCode, $item)) {
                yield $attributeCode => $item[$attributeCode];
            }
        }
    }

    public function revert(array $item): array
    {
        $container = $this->getOption('container');

        $output = [];
        foreach ($this->getOption('properties') as $masterKey) {
            // if quantified_associations is present, we need to handle it separately
            if ($masterKey === 'quantified_associations' && !empty($item['quantified_associations'])) {
                // we need adapt to th following structure
                // - quantified_associations|<association_code>|products|identifier becomes a comma separated list <association_code>-products
                // - quantified_associations|<association_code>|products|quantity becomes a comma separated list <association_code>-products-quantity
                foreach ($item['quantified_associations'] as $associationCode => $associationData) {
                    if (isset($associationData['products']) && is_array($associationData['products'])) {
                        $output[$associationCode.'-products'] = implode(',', array_column($associationData['products'], 'identifier'));
                        $output[$associationCode.'-products-quantity'] = implode(',', array_column($associationData['products'], 'quantity'));
                    }
                    if (isset($associationData['product-models']) && is_array($associationData['product-models'])) {
                        $output[$associationCode.'-product-models'] = implode(',', array_column($associationData['product-models'], 'identifier'));
                        $output[$associationCode.'-product-models-quantity'] = implode(',', array_column($associationData['product-models'], 'quantity'));
                    }
                }
                unset($item['quantified_associations']);
            }
            if ($masterKey === 'associations' && !empty($item['associations'])) {
                // we need adapt to the following structure
                // - associations|<association_code>|products becomes a comma separated list <association_code>-products
                foreach ($item['associations'] as $associationCode => $associationData) {
                    if (isset($associationData['products']) && is_array($associationData['products'])) {
                        $output[$associationCode.'-products'] = implode(',', $associationData['products']);
                    }
                    if (isset($associationData['product-models']) && is_array($associationData['product-models'])) {
                        $output[$associationCode.'-product-models'] = implode(',', $associationData['product-models']);
                    }
                }
                unset($item['associations']);
            }

            if (array_key_exists($masterKey, $item)) {
                $output[$masterKey] = $item[$masterKey];
                unset($item[$masterKey]);
            }
        }

        foreach ($item as $key => $itemValue) {
            $matcher = $itemValue['matcher'] ?? null;
            /** @var $matcher Matcher */
            if ($matcher && $matcher->matches($container)) {
                unset($itemValue['matcher']);
                unset($item[$key]);
                if (is_array($itemValue['data']) && array_key_exists('unit', $itemValue['data'])) {
                    $output[$matcher->getRowKey()] = $itemValue['data']['amount'];
                    $output[$matcher->getRowKey().'-unit'] = $itemValue['data']['unit'];
                    continue;
                }
                if (is_array($itemValue['data'])) {
                    $output[$matcher->getRowKey()] = implode(',', $itemValue['data']);
                    continue;
                }
                if (isset($itemValue['data'])) {
                    $output[$matcher->getRowKey()] = $itemValue['data'];
                }
            }
        }

        return $output;
    }

    public function getName(): string
    {
        return 'flat/akeneo/product/csv';
    }
}
