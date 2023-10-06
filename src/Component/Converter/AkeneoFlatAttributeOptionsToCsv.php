<?php

namespace Misery\Component\Converter;

use Misery\Component\Akeneo\Header\AkeneoHeaderTypes;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Reader\ReaderAwareInterface;
use Misery\Component\Reader\ReaderAwareTrait;

/**
 * This Converter converts flat product to std data
 * We focus on correcting with minimal issues
 * The better the input you give the better the output
 */
class AkeneoFlatAttributeOptionsToCsv implements ConverterInterface, ReadableInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private array $index = [];
    private $options = [
        'attribute_types:list' => [],
        'option_label_locales' => [],
        'option_label_field' => 'code',
        'option_field' => 'code',
    ];

    public function read(array $item): false|array
    {
        foreach ($this->convert($item) as $itemValue) {
            return $itemValue;
        }

        return false;
    }

    public function convert(array $item): array
    {
        $result = [];
        foreach ($item as $attributeCode => $itemValue) {
            $value = $this->getAkeneoDataStructure($attributeCode, $itemValue);
            if (!empty($value)) {
                $result[$attributeCode] = $value;
            }
        }

        return $result;
    }

    public function getAkeneoDataStructure(string $attributeCode, $value): array
    {
        $options = [];
        $locales = $this->getOption('option_label_locales');
        $labelField = $this->getOption('option_label_field');

        $type = $this->getOption('attribute_types:list')[$attributeCode] ?? null;
        if (null === $type) {
            return [];
        }

        switch ($type) {
            case AkeneoHeaderTypes::SELECT:
                break;
            case AkeneoHeaderTypes::MULTISELECT:
                //
                if (is_array($value)) {
                    foreach ($value as $valueSet) {

                        $optionValue = $valueSet[$this->getOption('option_field')] ?? null;
                        if (!$optionValue) {
                            continue;
                        }
                        $labels = [];
                        if (isset($valueSet[$labelField])) {
                            foreach ($locales as $locale) {
                                $labels[$locale] = $valueSet[$labelField];
                            }
                        }
                        $option = $this->generateOption(
                            $optionValue,
                            $attributeCode,
                            $labels
                        );
                        if (!in_array($option['id'], $this->index)) {
                            $this->index[$option['id']] = null;
                            $options[] = $option;
                        }
                    }
                }
                break;
            default:
                return [];
        }

        return $options;
    }

    private function generateOption(string $code, string $attributeCode, array $labels): array
    {
        return [
            'id' => "$code/$attributeCode",
            'code' => $code,
            'attribute' => $attributeCode,
            'sort_order' => count($this->index),
            'label' => $labels,
        ];
    }

    public function revert(array $item): array
    {
        return $item;
    }

    public function getName(): string
    {
        return 'flat/akeneo/attribute_option/csv';
    }
}