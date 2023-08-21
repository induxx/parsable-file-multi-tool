<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class PopAction implements OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'pop';

    /** @var array */
    private $options = [
        'field' => null,
        'separator' => null,
    ];

    public function apply(array $item): array
    {
        $field = $this->getOption('field');
        if (false === array_key_exists($field, $item)) {
            return $item;
        }

        $value = $item[$this->options['field']];
        $value = explode($this->options['separator'], $value);
        $item[$field] = array_pop($value);

        return $item;
    }
}