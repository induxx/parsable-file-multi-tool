<?php

namespace Misery\Component\Action;

use Misery\Component\Akeneo\DataStructure\AkeneoItemBuilder;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Configurator\ConfigurationAwareInterface;
use Misery\Component\Configurator\ConfigurationTrait;
use Misery\Model\DataStructure\ItemInterface;

class MakeItemAction implements OptionsInterface, ConfigurationAwareInterface
{
    use OptionsTrait;
    use ConfigurationTrait;

    public const NAME = 'make_item';

    /** @var array */
    private $options = [
        'domain' => 'akeneo',
        'class' => 'item',
        'attribute_types:list' => [],
    ];

    public function applyAsItem(ItemInterface $item): void
    {
    }

    public function apply(array $item): ItemInterface
    {
        $attributeTypes = $this->getOption('attribute_types:list');
        if ([] !== $attributeTypes) {
            $attributeTypes = $this->configuration->getList($attributeTypes);
        }

        if (isset($item['values'])) {
            return AkeneoItemBuilder::fromProductApiPayload($item, ['attribute_types' => $attributeTypes]);
        }

        return AkeneoItemBuilder::fromCatalogApiPayload($item, ['class' => $this->getOption('class')]);
    }
}