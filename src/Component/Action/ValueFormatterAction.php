<?php

namespace Misery\Component\Action;

use Misery\Component\AttributeFormatter\AttributeValueFormatter;
use Misery\Component\AttributeFormatter\BooleanAttributeFormatter;
use Misery\Component\AttributeFormatter\BooleanLabelsAttributeFormatter;
use Misery\Component\AttributeFormatter\MetricAttributeFormatter;
use Misery\Component\AttributeFormatter\MultiValuePresenterFormatter;
use Misery\Component\AttributeFormatter\NumberAttributeFormatter;
use Misery\Component\AttributeFormatter\PriceCollectionFormatter;
use Misery\Component\AttributeFormatter\PropertyFormatterRegistry;
use Misery\Component\AttributeFormatter\SimpleSelectAttributeFormatter;
use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Configurator\ConfigurationAwareInterface;
use Misery\Component\Configurator\ConfigurationTrait;
use Misery\Component\Converter\Matcher;
use Misery\Component\Reader\ItemReaderAwareInterface;
use Misery\Component\Reader\ReaderAwareTrait;
use Misery\Component\Source\SourceTrait;

class ValueFormatterAction implements OptionsInterface, ConfigurationAwareInterface
{
    use OptionsTrait;
    use ConfigurationTrait;

    public const NAME = 'value_format';
    private ?AttributeValueFormatter $attributeValueFormatter = null;

    /** @var array */
    private $options = [
        'fields' => [],
        'list' => null,
        'context' => [],
        'format_key' => null,
        'filter_list' => [],
    ];

    public function apply(array $item): array
    {
        $section = $this->getOption('section');
        $context = $this->getOption('context');

        $fields = $this->getOption('list', $this->getOption('fields'));

        if ($this->attributeValueFormatter === null) {
            $registry = new PropertyFormatterRegistry();
            $registry->addAll(
                new BooleanLabelsAttributeFormatter(),
                new BooleanAttributeFormatter(),
                new MetricAttributeFormatter(),
                new MultiValuePresenterFormatter(),
                new NumberAttributeFormatter(),
                new PriceCollectionFormatter(),
            );

            // Single source allowed ATM, else giant refactor
            if (isset($context['pim_catalog_simpleselect']['source'])) {
                $sourceAlias = $context['pim_catalog_simpleselect']['source'];
                $registry->addAll(
                    new SimpleSelectAttributeFormatter($this->getConfiguration()->getSources()->get($sourceAlias))
                );
            }

            $this->attributeValueFormatter = new AttributeValueFormatter($registry);
            $this->attributeValueFormatter->setAttributeTypesAndCodes($this->getOption('filter_list'));
        }


        // key represents the references that could match item keys
        foreach ($fields as $field) {
            // converted data
            $key = $this->findMatchedValueData($item, $field);
            if ($key && $this->attributeValueFormatter->needsFormatting($field)) {
                $item[$key]['data'] = $this->attributeValueFormatter->format($field, $item[$key]['data'], $context);
                continue;
            }
            // flat data
            $itemValue = $item[$field] ?? null;
            if ($itemValue && $this->attributeValueFormatter->needsFormatting($field)) {
                $item[$field] = $this->attributeValueFormatter->format($field, $item[$field], $context);
            }
        }

        return $item;
    }

    private function findMatchedValueData(array $item, string $field)
    {
        foreach ($item as $key => $itemValue) {
            $matcher = $itemValue['matcher'] ?? null;
            /** @var $matcher Matcher */
            if ($matcher && $matcher->matches($field)) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Akeneo Product Values are always structured with at least :
     * - 1 element with start index 0
     * - the element always contains a "data" property
     *
     * From here we still don't know the elements scope, [global, scopable, localizable, scopable+localizable]
     */
    private function isAkeneoApiValue($itemValue)
    {
        return key_exists('data', $itemValue[0]);
    }

    private function getAkeneoValue($itemValue, int $i = 0)
    {
        return $itemValue[$i]['data'];
    }

    private function setAkeneoValue($itemValue, $changedValue, int $i = 0)
    {
        $itemValue[$i]['data'] = $changedValue;

        return $itemValue;
    }
}