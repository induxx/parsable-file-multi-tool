<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Model\DataStructure\ItemInterface;

class FormatAction implements OptionsInterface, ActionItemInterface
{
    use OptionsTrait;

    private $mapper;

    public const NAME = 'format';

    /** @var array */
    private $options = [
        'field' => null,
        'functions' => [],
        'decimals' => 4,
        'index' => null,
        'decimal_sep' => '.',
        'mille_sep' => ',',
        'multi_value' => true,
    ];

    public function apply(array $item): array
    {
        $field = $this->getOption('field', $this->getOption('list'));

        if (is_array($field)) {
            foreach ($field as $fieldValue) {
                $this->setOption('field', $fieldValue);
                $item = $this->apply($item);
            }
            $this->setOption('field', $field);

            return $item;
        }

        // type validation
        if (!isset($item[$field])) {
            return $item;
        }
        if (is_array($item[$field]) && $this->getOption('multi_value')) {
            $item[$field] = array_map(function ($value) {
                return $this->doApply($value);
            }, $item[$field]);
            return $item;
        }

        $item[$field] = $this->doApply($item[$field]);

        return $item;
    }

    public function applyAsItem(ItemInterface $item): void
    {
        $field = $this->getOption('field', $this->getOption('list'));

        if (is_array($field)) {
            foreach ($field as $fieldValue) {
                $this->setOption('field', $fieldValue);
                $this->applyAsItem($item);
            }
            $this->setOption('field', $field);

            return;
        }

        $itemNode = $item->getItem($field);
        if (!$itemNode) {
            return;
        }

        $dataValue = $itemNode->getDataValue();
        if (is_array($dataValue) && $this->getOption('multi_value')) {
            $item->editItemValue($itemNode->getCode(), array_map(function ($value) {
                return $this->doApply($value);
            }, $dataValue));
            return;
        }

        $item->editItemValue($itemNode->getCode(), $this->doApply($dataValue));
    }

    private function doApply($value)
    {
        foreach ($this->getOption('functions') as $function) {
            switch ($function) {
                case 'replace':
                    if ($this->getOption('search') && $this->getOption('replace') !== null) {
                        $value = str_replace($this->getOption('search'), $this->getOption('replace'), $value);
                    }
                    break;
                case 'number':
                    if (is_numeric($value)) {
                        $value = number_format(
                            $value,
                            $this->getOption('decimals'),
                            $this->getOption('decimal_sep'),
                            $this->getOption('mille_sep')
                        );
                    }
                    break;
                case 'explode':
                    $value = explode($this->getOption('separator'), $value);
                    break;
                case 'select_index':
                    if (null !== $this->getOption('index')) {
                        $value = $value[$this->getOption('index')] ?? $value;
                    }
                    break;
                case 'sprintf':
                    $value = sprintf($this->getOption('format'), $value);
                    break;
                case 'prefix':
                    $value = $this->getOption('prefix'). ltrim($value, $this->getOption('prefix'));
                    break;
                case 'suffix':
                    $suffix = $this->getOption('suffix');
                    $value = (!str_ends_with($value, $suffix)) ? $value . $suffix : $value;
                    break;
                case 'substr':
                    $value = substr($value, $this->getOption('offset'), $this->getOption('length'));
                    break;
                default:
                    break;
            }
        }

        return $value;
    }
}
