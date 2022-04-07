<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Reader\ItemReaderAwareInterface;
use Misery\Component\Reader\ItemReaderAwareTrait;

class FormatAction implements OptionsInterface, ItemReaderAwareInterface
{
    use OptionsTrait;
    use ItemReaderAwareTrait;

    private $mapper;

    public const NAME = 'format';

    /** @var array */
    private $options = [
        'field' => null,
        'functions' => [],
    ];

    public function apply(array $item): array
    {
        $functions = $this->options['functions'];
        $field = $this->options['field'];

        // type validation
        if (!isset($item[$field])) {
            return $item;
        }

        foreach ($functions as $function) {
            switch ($function) {
                case 'explode':
                    $item[$field] = explode($this->options['separator'], $item[$field]);
                    break;
                case 'implode':
                    if (isset($this->options['locales'])) {
                        foreach ($this->options['locales'] as $locale) {
                            $item[$field][$locale] = implode($this->options['separator'], $item[$field][$locale]);
                        }

                        break;
                    }
                    
                    $item[$field] = implode($this->options['separator'], $item[$field]);
                    break;
                case 'select_index':
                    $item[$field] = $item[$field][$this->options['index']];
                    break;
                case 'sprintf':
                    $item[$field] = sprintf($this->options['format'], $item[$field]);
                    break;
                default:
                    break;
            }
        }

        return $item;
    }
}