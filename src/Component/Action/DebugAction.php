<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class DebugAction implements OptionsInterface, ActionInterface
{
    use OptionsTrait;

    public const NAME = 'debug';

    /** @var array */
    private $options = [];

    public function apply(array $item): array
    {
        dd($item);
    }
}