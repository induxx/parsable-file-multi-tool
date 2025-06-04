<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Configurator\ConfigurationAwareInterface;
use Misery\Component\Configurator\ConfigurationTrait;

class UnsetAction implements OptionsInterface, ConfigurationAwareInterface
{
    use OptionsTrait;
    use ConfigurationTrait;

    public const NAME = 'unset';

    /** @var array */
    private $options = [
        'field' => null,
        'list' => null,
        'unset_list_keys' => false,
        'empty_only' => 0
    ];

    public function apply(array $item): array
    {
        $field = $this->getOption('field');
        $list = $this->getOption('list');
        $unsetListKey = $this->getOption('unset_list_keys');

        // validation
        if ($field && !is_array($item[$field])) {
            return $item;
        }

        if ($unsetListKey) {
            $item[$field] = array_values(
                array_diff($item[$field], $list)
            );
            return $item;
        }

        foreach ($this->getOption('list') as $key) {
            if ((bool)$this->getOption('empty_only') && !empty($item[$field][$key])) {
                continue;
            }

            unset($item[$field][$key]);
        }

        return $item;
    }
}