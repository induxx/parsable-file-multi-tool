<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class StartFromFieldAction implements OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'start_from_field';

    /** @var array */
    private $options = [
        'from' => null,
    ];

    public function apply(array $item): array
    {
        $fromField = $this->getOption('from');
        if (null === $fromField) {
            return $item;
        }

        if (is_string($fromField) && isset($item[$fromField]) && is_array($item[$fromField])) {
            $item = $item[$fromField];
        }

        return $item;
    }
}