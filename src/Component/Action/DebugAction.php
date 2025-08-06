<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Model\DataStructure\ItemInterface;

class DebugAction implements OptionsInterface, ActionInterface, ActionItemInterface
{
    use OptionsTrait;

    public const NAME = 'debug';

    /** @var array */
    private $options = [
        'field' => null,
        'until_field' => null,
        'marker' => null,
    ];

    private int $applyCount = 0;

    public function applyAsItem(ItemInterface $item): void
    {
        dd($item);
    }

    public function apply(array $item): array
    {
        $this->applyCount++;

        $marker = $this->getOption('marker');
        if ($marker) {
            [$filename, $line] = explode(':', $marker);
            if ($this->applyCount <= (int) $line) {
                return $item;
            }
        }

        $untilField = $this->getOption('until_field');
        if ($untilField && isset($item[$untilField])) {
            dd($item[$untilField]);
        }
        $field = $this->getOption('field');
        if ($field) {
            dd($item[$field]);
        }
        if (null === $field && null === $untilField) {
            dd($item);
        }

        return $item;
    }
}