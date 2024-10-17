<?php

namespace Misery\Component\Action;

use App\Component\ChangeManager\ChangeSetLabelMaker;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Configurator\ConfigurationAwareInterface;
use Misery\Component\Configurator\ConfigurationTrait;
use Misery\Component\Converter\Matcher;

class StoreAction implements ActionInterface, OptionsInterface, ConfigurationAwareInterface
{
    use OptionsTrait;
    use ConfigurationTrait;

    public const PRODUCT_HAS_CHANGES = 'product_has_changes';
    public const DELETE_PRODUCT = 'delete_product';
    public const UPDATE_PRODUCT = 'update_product';
    public const STORE_PRODUCT = 'store_product';

    public const NAME = 'store';
    public ItemActionProcessor $trueActionProcessor;
    public ItemActionProcessor $falseActionProcessor;

    /** @var array */
    private $options = [
        'event' => null,
        'identifier' => null,
        'change_manager' => [
            'all_values' => true,
        ],
        'init' => false,
        'true_action' => [],
        'false_action' => [],
    ];

    public function init(): void
    {
        $init = $this->getOption('init');
        if (false === $init) {
            $trueAction = $this->getOption('true_action');
            $falseAction = $this->getOption('false_action');
            if ([] !== $trueAction) {
                $this->trueActionProcessor = $this->configuration->generateActionProcessor($trueAction);
            }
            if ([] !== $falseAction) {
                $this->falseActionProcessor = $this->configuration->generateActionProcessor($falseAction);
            }
            $this->setOption('init', true);            
        }
    }

    public function apply(array $item): array
    {
        $this->init();

        $identifier = 'product:'.$item['identifier']; // $this->getOption('identifier');
        $event = $this->getOption('event');
        if (empty($identifier) || empty($event)) {
            return $item;
        }
        $changeManager = $this->configuration->changeManager;

        if ($event === self::PRODUCT_HAS_CHANGES) {
            $trueAction = $this->getOption('true_action');
            $falseAction = $this->getOption('false_action');
            $changeManagerData = $this->getOption('change_manager');

            // label maker process based on change_manager array
            $labels = (new ChangeSetLabelMaker('product'));
            if (true === $changeManagerData['all_values'] && isset($item['values'])) {
                $labels->addSubDomainProperties('values', array_keys($item['values']));
            }
            $labels = $labels->build();

            if ($changeManager->hasChanges($identifier, $item, $labels)) {
                // start true_action
                // make changes list
                $changes = $changeManager->getChanges($identifier, 'product.values');
                $this->configuration->addLists([
                    'product_changes_fields_added' => $changes['added'],
                    'product_changes_fields_deleted' => $changes['deleted'],
                    'product_changes_fields_updated' => $changes['updated'],
                    'product_changes_fields_all' => $changes['all'],
                ]);

                // see GroupAction, get ActionProcessor, process your action(s)
                if ([] !== $trueAction) {
                    $item = $this->trueActionProcessor->process($item);
                }

                //$this->storeProduct($identifier);
            } else {
                // see GroupAction, get actionProcessor, process your action(s)
                if ([] !== $falseAction) {
                    $item = $this->falseActionProcessor->process($item);
                }

                // start false_action if any
            }
        }

        return $item;
    }

    private function storeProduct(string $identifier): void
    {
        $this->configuration->changeManager->persistChange($identifier);
    }
}