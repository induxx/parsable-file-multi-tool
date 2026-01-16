<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Format\Format;
use Misery\Component\Common\Modifier\CellModifier;
use Misery\Component\Common\Modifier\RowModifier;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\Registry;
use Misery\Model\DataStructure\ItemInterface;

class ModifierAction implements OptionsInterface, ActionItemInterface
{
    use OptionsTrait;

    public const NAME = 'modify';

    /** @var RowModifier|CellModifier */
    private $modifier;
    /** @var Format */
    private $formatter;

    private Registry $modifierRegistry;
    private Registry $formatRegistry;

    /** @var array */
    private $options = [
        'modifier' => null,
        'formatter' => null,
        'keys' => null,
        'list' => null,
    ];

    public function __construct(Registry $modifierRegistry, Registry $formatRegistry)
    {
        $this->modifierRegistry = $modifierRegistry;
        $this->formatRegistry = $formatRegistry;
    }

    public function apply($item): array
    {
        // this should be part of the prepare state
        // when we set the options
        // so don't need to check on every action::apply
        $keys = $this->getOption('list') ?? explode(',', $this->getOption('keys'));

        foreach ($keys as $key) {
            $listItem = $item[$key] ?? null;
            /** @var RowModifier|CellModifier $modifier */
            if (null !== $listItem && $this->options['modifier'] && $modifier = $this->getModifier($this->options['modifier'])) {
                if (is_array($listItem)) {
                    $item[$key] = array_map(function ($itemValue) use ($modifier) {
                        return is_string($itemValue) ? $modifier->modify($itemValue) : null;
                    }, $listItem);
                    continue;
                }
                $item[$key] = $modifier->modify($listItem);
            }
            if (null !== $listItem && $this->options['formatter'] && $formatter = $this->getFormatter($this->options['formatter'])) {
                if (is_array($listItem)) {
                    $item[$key] = array_map(function ($itemValue) use ($formatter) {
                        return is_string($itemValue) ? $formatter->format($itemValue) : $formatter->reverseFormat($itemValue);
                    }, $listItem);
                    continue;
                }

                $item[$key] = is_string($listItem) ? $formatter->format($listItem) : $formatter->reverseFormat($listItem);
            }
        }

        return $item;
    }

    public function applyAsItem(ItemInterface $item): void
    {
        $keys = $this->getOption('list') ?? explode(',', $this->getOption('keys'));

        foreach ($keys as $key) {
            $itemNode = $item->getItem($key);
            if ($itemNode === null) {
                continue;
            }

            $dataValue = $itemNode->getDataValue();
            if (null !== $dataValue && $this->options['modifier'] && $modifier = $this->getModifier($this->options['modifier'])) {
                if (is_array($dataValue)) {
                    $item->editItemValue($itemNode->getCode(), array_map(function ($itemValue) use ($modifier) {
                        return is_string($itemValue) ? $modifier->modify($itemValue) : null;
                    }, $dataValue));
                } else {
                    $item->editItemValue($itemNode->getCode(), $modifier->modify($dataValue));
                }
            }

            if (null !== $dataValue && $this->options['formatter'] && $formatter = $this->getFormatter($this->options['formatter'])) {
                if (is_array($dataValue)) {
                    $item->editItemValue($itemNode->getCode(), array_map(function ($itemValue) use ($formatter) {
                        return is_string($itemValue) ? $formatter->format($itemValue) : $formatter->reverseFormat($itemValue);
                    }, $dataValue));
                } else {
                    $item->editItemValue($itemNode->getCode(), is_string($dataValue) ? $formatter->format($dataValue) : $formatter->reverseFormat($dataValue));
                }
            }
        }
    }

    private function getModifier(string $modifierName)
    {
        if (null === $this->modifier) {
            if ($this->modifier = $this->modifierRegistry->filterByAlias($modifierName)) {
                if ($this->modifier instanceof OptionsInterface) {
                    $this->modifier->setOptions($this->options);
                }
            }
        }

        return $this->modifier;
    }

    private function getFormatter(string $formatName)
    {
        if (null === $this->formatter) {
            if ($this->formatter = $this->formatRegistry->filterByAlias($formatName)) {
                if ($this->formatter instanceof OptionsInterface) {
                    $this->formatter->setOptions($this->options);
                }
            }
        }

        return $this->formatter;
    }
}
