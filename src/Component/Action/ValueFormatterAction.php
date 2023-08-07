<?php

namespace Misery\Component\Action;

use Misery\Component\AttributeFormatter\AttributeValueFormatter;
use Misery\Component\AttributeFormatter\BooleanAttributeFormatter;
use Misery\Component\AttributeFormatter\BooleanLabelsAttributeFormatter;
use Misery\Component\AttributeFormatter\MetricAttributeFormatter;
use Misery\Component\AttributeFormatter\NumberAttributeFormatter;
use Misery\Component\AttributeFormatter\PriceCollectionFormatter;
use Misery\Component\AttributeFormatter\PropertyFormatterRegistry;
use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class ValueFormatterAction implements OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'value_format';
    private ?AttributeValueFormatter $attributeValueFormatter = null;

    /** @var array */
    private $options = [
        'fields' => [],
        'context' => [],
        'format_key' => null,
        'list' => [],
    ];

    public function apply(array $item): array
    {
        $index = 'values';
        if ($this->attributeValueFormatter === null) {
            $registry = new PropertyFormatterRegistry();
            $registry->addAll(
                new BooleanLabelsAttributeFormatter(),
                new BooleanAttributeFormatter(),
                new MetricAttributeFormatter(),
                //new MultiValuePresenterFormatter(),
                new NumberAttributeFormatter(),
                new PriceCollectionFormatter(),
            );
            $this->attributeValueFormatter = new AttributeValueFormatter($registry);
            $this->attributeValueFormatter->setAttributeTypesAndCodes($this->getOption('list'));
        }

        // key represents the references that could match item keys
        foreach ($this->getOption('fields') as $field) {
            if (isset($item[$index][$field]) && $this->attributeValueFormatter->needsFormatting($field)) {
                $itemValue = $item[$index][$field];
                $context = $this->getOption('context');
                $item[$index][$field] = $this->attributeValueFormatter->format($field, $itemValue, $context);
            }
        }

        return $item;
    }
}