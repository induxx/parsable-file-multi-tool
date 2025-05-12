<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Model\DataStructure\ItemInterface;

class ListMapperAction implements OptionsInterface, ActionItemInterface
{
    use OptionsTrait;

    public const NAME = 'value_mapping_in_list';

    /** @var array */
    private $options = [
        'field' => null,
        'fields' => [],
        'store_field' => null,
        'list' => [],
    ];

    public function applyAsItem(ItemInterface $item): void
    {
        if ([] === $this->getOption('list')) {
            return;
        }

        if (null === $this->getOption('field')) {
            return;
        }
        $field = $this->getOption('field');
        $storeField = $this->getOption('store_field');

        $itemNode = $item->getItem($field);
        $dataValue = $itemNode?->getDataValue();
        if ($dataValue && array_key_exists($dataValue, $this->getOption('list'))) {
            $newValue = $this->options['list'][$dataValue];
            if ($storeField) {
                $item->copyItem($storeField, $newValue);
            } else {
                $item->editItemValue($field, $newValue);
            }
        }
    }

    /**
     * @param  array<string, mixed> $item
     * @return array<string, mixed>
     */
    public function apply(array $item): array
    {
        if ([] === $this->getOption('list')) {
            return $item;
        }

        // Cache option lookups once
        $field      = $this->getOption('field');
        $storeField = $this->getOption('store_field', $field);
        $list       = $this->getOption('list');
        $fields     = $this->getOption('fields');

        if ($fields !== []) {
            $this->setOption('fields', []);
            foreach ($fields as $field) {
                $this->setOption('field', $field);
                $item = $this->apply($item);
            }
            return $item;
        }

        // Nothing to apply if options are missing or list is empty
        if (null === $field || null === $storeField || empty($list)) {
            return $item;
        }

        // Grab the incoming value (or null if missing)
        $value = $item[$field] ?? null;

        // Only write back when we actually have a known key in the map
        if ($value !== null && isset($list[$value])) {
            $item[$storeField] = $list[$value];
        }

        return $item;
    }
}