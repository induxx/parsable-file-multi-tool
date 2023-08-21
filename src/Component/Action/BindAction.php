<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Reader\ItemReaderAwareInterface;
use Misery\Component\Reader\ItemReaderAwareTrait;
use Misery\Component\Source\SourceFilter;

class BindAction implements OptionsInterface
{
    use OptionsTrait;

    private $mapper;

    public const NAME = 'bind';

    /** @var array */
    private $options = [
        'list' => [],
        'filter' => null,
    ];

    # Usage

    public function apply(array $item): array
    {
        /** @var SourceFilter $filter */
        $filter = $this->getOption('filter');

        foreach ($this->getOption('list') as $field) {
            if (array_key_exists($field, $item)) {
                $item[$field] = $filter->filter($item);
                if (count($item[$field]) <= 1) {
                    $item[$field] = current($item[$field]);
                }
            }
        }

        return $item;
    }
}