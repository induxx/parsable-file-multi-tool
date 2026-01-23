<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Utils\ValueFormatter;
use Misery\Component\Configurator\ConfigurationAwareInterface;
use Misery\Component\Configurator\ConfigurationTrait;
use Misery\Component\Statement\StatementBuilder;
use Misery\Model\DataStructure\ItemInterface;

class StatementAction implements OptionsInterface, ConfigurationAwareInterface, ActionItemInterface
{
    use ConfigurationTrait;
    use OptionsTrait;

    private $statement;

    public const NAME = 'statement';

    /** @var array */
    private $options = [
        'when' => [],
        'then' => [],
    ];

    public function apply(array $item): array
    {
        $when = $this->getOption('when');
        $then = $this->getOption('then');

        $context = [];
        if (isset($when['context']['list'])) {
            if (is_string($when['context']['list'])) {
                $context['list'] = $this->configuration->getList($when['context']['list']);
            }
            if (is_array($when['context']['list'])) {
                $context['list'] = $when['context']['list'];
            }
        }

        $statement = StatementBuilder::build($when, $context);
        if (isset($then['action'])) {
            if ($then['action'] === 'skip') {
                $action = new SkipAction();
                $message = $then['skip_message'] ?? '';
                if (!empty($message)) {
                    $message = ValueFormatter::format($message, $item);
                }
                $action->setOptions(['skip_message' => $message, 'force_skip' => true]);
            }
            if ($then['action'] === 'copy') {
                $action = new CopyAction();
                $action->setOptions($then);
            }
            if ($then['action'] === 'debug') {
                $action = new DebugAction();
            }

            if ($then['action'] === 'remove') {
                $action = new RemoveAction();
                $action->setOptions($then);
            }

            if ($then['action'] === 'combine') {
                $action = new CombineAction();
                $action->setOptions($then);
            }

            if ($then['action'] === 'concat') {
                $action = new ConcatAction();
                $action->setOptions($then);
            }

            $statement->setAction($action);
        }

        if (isset($then['field'], $then['state'])) {
            $statement->then($then['field'], $then['state'] ?? null);
        } else {
            foreach ($then as $thenField => $thenState) {
                // tmp fix for combine action because its an array
                if ($thenField === 'keys') {
                    return $statement->apply($item);
                }

                $statement->then($thenField, $thenState ?? null);
            }
        }

        return $statement->apply($item);
    }

    public function applyAsItem(ItemInterface $item): void
    {
        $original = $item->toArray();
        $updated = $this->apply($original);

        foreach ($updated as $key => $value) {
            if ($key === 'values' || $key === 'labels') {
                continue;
            }

            if ($item->hasItem($key)) {
                $item->editItemValue($key, $value);
                continue;
            }

            $item->addItem($key, $value);
        }

        foreach ($original as $key => $value) {
            if ($key === 'values' || $key === 'labels') {
                continue;
            }

            if (!array_key_exists($key, $updated)) {
                $item->removeItem($key);
            }
        }
    }
}
