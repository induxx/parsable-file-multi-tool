<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class DebugAction implements OptionsInterface, ActionInterface
{
    use OptionsTrait;

    public const NAME = 'debug';

    /** @var array */
    private $options = [
        'field' => null,
        'until_field' => null,
    ];

    public function apply(array $item): array
    {
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