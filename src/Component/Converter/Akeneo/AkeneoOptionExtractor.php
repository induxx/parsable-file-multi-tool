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
 * This Converter extracts the options from the Akeneo Product data structure
 */
class AkeneoOptionExtractor implements ConverterInterface, ItemCollectionLoaderInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private $options = [
        'attribute_option_codes:list' => [],
        'parent_identifier' => 'attribute',
        'identifier' => 'code',
        'reference' => 'reference',
        'reference_code' => true, # force the option code to be a reference code
        'lower_cased' => true, # force the option code to be lower cased
    ];

    public function init(): void
    {
    }

    public function convert(array $item): array
    {
        $identifier = $this->getOption('identifier');
        $parentIdentifier = $this->getOption('parent_identifier');
        $reference = $this->getOption('reference');
        $optionCodes = $this->getOption('attribute_option_codes:list');
        $referenceCode = $this->getOption('reference_code');
        $lowerCased = $this->getOption('lower_cased');

        // first we reduce the item to only the options
        $item = ColumnReducer::reduceItem($item, ...$optionCodes);

        // second we remove the null values
        $item = array_filter($item);

        $result = [];
        if ($item === []) {
            return $result;
        }

        // third we re-loop the item to create the options structure
        foreach ($item as $key => $value) {
            if (empty($value)) {
                continue;
            }
            if (is_array($value)) {
                foreach ($value as $option) {
                    $optionCode = $this->renderCode($option, $referenceCode, $lowerCased);
                    $result[$key][$parentIdentifier] = $key;
                    $result[$key][$identifier] = $optionCode;
                    $result[$key][$reference] = $optionCode . '-'. $key;
                }
            } else {
                $optionCode = $this->renderCode($value, $referenceCode, $lowerCased);
                $result[$key][$parentIdentifier] = $key;
                $result[$key][$identifier] = $optionCode;
                $result[$key][$reference] = $optionCode . '-'. $key;
            }
        }

        return $result;
    }

    private function renderCode(string $value, bool $referenceCode = true, bool $lowerCase = true): string
    {
        if ($referenceCode) {
            $value = (new ReferenceCodeModifier())->modify($value);
        }
        if ($lowerCase) {
            $value = strtolower($value);
        }

        return $value;
    }

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