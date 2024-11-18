<?php

namespace Misery\Component\Action;

use Misery\Component\Akeneo\DataStructure\AkeneoItemFactory;
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
        'attribute_types:list' => [],
    ];

    public function applyAsItem(ItemInterface $item): array
    {
        $fields = [];
        foreach ($item->getItemNodes() as $code => $fieldValue) {
            $matcher = $fieldValue->getMatcher();

            if ($matcher->matches('values')) {
                $fields['values'][$matcher->getPrimaryKey()][] = $fieldValue->getValue();
            } else {
                $fields[$code] = $fieldValue->getValue();
            }
        }

        return $fields;
    }

    public function apply(array $item): ItemInterface
    {
        $attributeTypes = $this->getOption('attribute_types:list');
        $attributeTypes = $this->configuration->getList($attributeTypes);

        return AkeneoItemFactory::create($item, ['attribute_types' => $attributeTypes]);
    }
}