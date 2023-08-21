<?php

namespace Misery\Component\Action;

use Misery\Component\Akeneo\AkeneoValuePicker;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Utils\ValueFormatter;
use Misery\Component\Reader\ItemCollection;
use Misery\Component\Reader\ItemReader;
use Misery\Component\Reader\ItemReaderAwareInterface;
use Misery\Component\Reader\ItemReaderAwareTrait;

class ConcatAction implements OptionsInterface
{
    use OptionsTrait;
    private $repo;
    private $prepReader;

    public const NAME = 'concat';

    /** @var array */
    private $options = [
        'key' => null,
        'field' => null,
        'format' => '%s',
    ];

    public function apply(array $item): array
    {
        $field = $this->getOption('field', $this->getOption('key'));

        if (null == $field) {
            return $item;
        }

        if (array_key_exists($field, $item)) {
            $item[$field] = ValueFormatter::format($this->getOption('format'), $item);
        }

        return $item;
    }
}