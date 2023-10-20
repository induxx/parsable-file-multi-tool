<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Configurator\ConfigurationAwareInterface;
use Misery\Component\Configurator\ConfigurationTrait;

class ListAction implements OptionsInterface, ConfigurationAwareInterface
{
    use OptionsTrait;
    use ConfigurationTrait;

    public const NAME = 'list';

    /** @var array */
    private $options = [
        'name' => null,
    ];
    private $filterAction;

    public function apply($item)
    {
        $listName = $this->getOption('name');
        if (null ===$listName) {
            return $item;
        }
        if (null === $this->filterAction) {
            $filterAction = new FilterFieldAction();
            $filterAction->setOptions($this->getOptions());
            $this->filterAction = $filterAction;
        }

        $this->getConfiguration()->addLists([$listName => $this->filterAction->apply($item)]);

        return $item;
    }
}