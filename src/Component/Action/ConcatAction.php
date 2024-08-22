<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Utils\ValueFormatter;

class ConcatAction implements OptionsInterface
{
    use OptionsTrait;
    private $repo;
    private $prepReader;

    public const NAME = 'concat';

    /** @var array */
    private $options = [
        'key' => null,
        'format' => '%s',
    ];

    public function apply(array $item): array
    {
        $field = $this->getOption('field', $this->getOption('key'));
        if (null == $field) {
            return $item;
        }

        // don't check if array_key_exist here, concat should always work, if the field doesn't exist
        // somewhat the point to form a new field from concatination
        $item[$field] = ValueFormatter::format($this->getOption('format'), $item);

        return $item;
    }
}