<?php

namespace Misery\Component\Converter\Akeneo;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Converter\ConverterInterface;
use Misery\Component\Converter\ItemCollectionLoaderInterface;
use Misery\Component\Filter\ColumnReducer;
use Misery\Component\Modifier\ReferenceCodeModifier;
use Misery\Component\Reader\ItemCollection;

/**
 * This case is not ready for scope-able options, nor API options
 * This Converter extracts the options from the Akeneo Product data structure
 */
class AkeneoOptionExtractor implements ConverterInterface, ItemCollectionLoaderInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private $options = [
        'attribute_option_codes:list' => [],
        'parent_identifier_field' => 'attribute',
        'identifier_field' => 'code',
        'reference_field' => 'reference',
        'separator' => ',',
        'has_string_separator' => false, # LEGACY if the value is a string and contains the separator, explode it
        'reference_code' => true, # force the option code to be a reference-able code
        'reference_code_pattern' => 'old_pattern', # the pattern to use for the reference code
        'lower_cased' => true, # force the option code to be lower cased
        'include_original_reference' => false, # include the original reference in the output
        'mappings' => [],
    ];

    public function convert(array $item): array
    {
        $identifierField = $this->getOption('identifier_field');
        $parentIdentifierField = $this->getOption('parent_identifier_field');
        $referenceField = $this->getOption('reference_field');
        $optionCodes = $this->getOption('attribute_option_codes:list');
        $referenceCode = $this->getOption('reference_code');
        $lowerCased = $this->getOption('lower_cased');
        $separator = $this->getOption('separator'); # legacy
        $separators = is_array($separator) ? $separator : [$separator];
        $hasStringSeparator = $this->getOption('has_string_separator');
        $mappings = $this->getOption('mappings');
        $includeOriginalReference = $this->getOption('include_original_reference');

        // Reduce the item to only the options
        $item = ColumnReducer::reduceItem($item, ...$optionCodes);

        // Remove null values
        $item = array_filter($item);

        $result = [];
        if ($item === []) {
            return $result;
        }

        // Create the options structure
        foreach ($item as $key => $value) {
            if (empty($value)) {
                continue;
            }
            // transform the value into an array if it is a string and contains the separator
            if ($hasStringSeparator) {
                $value = $this->separateValue($value, $separators);
            }

            if (is_array($value)) {
                foreach ($value as $option) {
                    $option = trim($option);
                    if (empty($option)) {
                        continue;
                    }
                    $optionCode = $this->renderCode($option, $referenceCode, $lowerCased);
                    if (isset($mappings[$key][$optionCode])) {
                        $optionCode = $mappings[$key][$optionCode];
                    }
                    $id = $optionCode . '-'. $key;
                    $result[$id][$parentIdentifierField] = $key;
                    $result[$id][$identifierField] = $optionCode;
                    $result[$id][$referenceField] = $id;
                    if ($includeOriginalReference) $result[$id]['original_reference'] = $option . '-' . $key;
                    $result[$id]['original_value'] = $option;
                }
            } else {
                $value = trim($value);
                if (empty($value)) {
                    continue;
                }
                $optionCode = $this->renderCode($value, $referenceCode, $lowerCased);
                if (isset($mappings[$key][$optionCode])) {
                    $optionCode = $mappings[$key][$optionCode];
                }
                $id = $optionCode . '-'. $key;
                $result[$id][$parentIdentifierField] = $key;
                $result[$id][$identifierField] = $optionCode;
                $result[$id][$referenceField] = $id;
                if ($includeOriginalReference) $result[$id]['original_reference'] = $value . '-' . $key;
                $result[$id]['original_value'] = $value;
            }
        }

        return $result;
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
     * Render the option code.
     * This method is used to render the option code into a acceptable format for akeneo.
     *
     * @param string $value The value to render.
     * @param bool $referenceCode Whether to use reference code.
     * @param bool $lowerCase Whether to convert to lower case.
     * @return string The rendered code.
     */
    private function renderCode(string $value, bool $referenceCode = true, bool $lowerCase = true): string
    {
        if ($referenceCode) {
            $mod = new ReferenceCodeModifier();
            $mod->setOption('use_pattern', $this->getOption('reference_code_pattern'));
            $value = $mod->modify($value);
        }
        if ($lowerCase) {
            $value = strtolower($value);
        }

        return $value;
    }

    /**
     * Load the given item array as an ItemCollection, exploding the item into multiple loop-able items.
     *
     * @param array $item The item to load.
     * @return ItemCollection The loaded item collection.
     */
    public function load(array $item): ItemCollection
    {
        return new ItemCollection($this->convert($item));
    }

    public function getName(): string
    {
        return 'akeneo/option/extractor';
    }

    public function revert(array $item): array
    {
        return $item;
    }
}