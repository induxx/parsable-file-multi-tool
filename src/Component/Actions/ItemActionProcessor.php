<?php

namespace Misery\Component\Actions;

use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Registry\RegistryInterface;
use Misery\Component\Reader\ItemReaderAwareInterface;
use Misery\Component\Source\SourceCollection;

class ItemActionProcessor
{
    private $registry;
    /** @var SourceCollection */
    private $sources;
    private $configurationRules;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function setContext(SourceCollection $sources, array $configuration)
    {
        $this->sources = $sources;
        $this->configurationRules = $this->prepRulesFromConfiguration($configuration);
    }

    private function prepRulesFromConfiguration(array $configuration): array
    {
        $rules = [];
        foreach ($configuration as $name => $value) {
            $action = $value['action'] ?? null;
            unset($value['action']);

            if ($action = $this->registry->filterByAlias($action)) {

                if ($action instanceof OptionsInterface && !empty($value)) {
                    $action->setOptions($value);
                }

                if ($action instanceof ItemReaderAwareInterface && isset($value['source'])) {
                    $action->setReader($this->sources->get($value['source'])->getReader());
                }

                $rules[$name] = $action;
            }
        }

        return $rules;
    }

    public function process(array $item): array
    {
        foreach ($this->configurationRules as $name => $action) {
            $item = $action->apply(ArrayFunctions::flatten($item, '-'));
        }

        return $item;
    }
}