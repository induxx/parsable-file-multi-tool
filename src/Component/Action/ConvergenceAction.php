<?php

namespace Misery\Component\Action;

use Misery\Component\AttributeFormatter\AttributeValueFormatter;
use Misery\Component\AttributeFormatter\BooleanLabelsAttributeFormatter;
use Misery\Component\AttributeFormatter\MetricAttributeFormatter;
use Misery\Component\AttributeFormatter\NumberAttributeFormatter;
use Misery\Component\AttributeFormatter\PriceCollectionFormatter;
use Misery\Component\AttributeFormatter\PropertyFormatterRegistry;
use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class ConvergenceAction implements OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'convergence';

    /** @var array */
    private $options = [
        'field' => null,
        'selection' => [],
        'item_sep' => ',',
        'key_value_sep' => ':',
        'encapsulate' => false,
        'encapsulation_char' => '"',
    ];

    public function apply(array $item): array
    {
        $field = $this->getOption('field');
        if (null === $field) {
            return $item;
        }

        $fields = $this->getOption('selection');
        $keyValueSeparator = trim($this->getOption('key_value_sep'));
        $elementSeparator = trim($this->getOption('item_sep'));
        $encapsulate = $this->getOption('encapsulate');
        $encapsulationChar = $this->getOption('encapsulation_char');

        $elementSeparator .= ' ';
        $keyValueSeparator .= ' ';

        $result = [];
        foreach ($this->getOption('selection') as $key) {
            $value = key_exists($key, $item) ? $item[$key] : null;
            // Include only the fields with assigned values
            if ($value !== null) {
                // Encapsulate keys and values if needed
                $key = $encapsulate ? $encapsulationChar . $key . $encapsulationChar : $key;
                $value = $encapsulate ? $encapsulationChar . $value . $encapsulationChar : $value;
                $result[] = $key . $keyValueSeparator . $value;
            }
        }

        $item[$field] = implode($elementSeparator, $result);

        return $item;
    }
}