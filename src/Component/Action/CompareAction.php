<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Utils\ValueFormatter;

class CompareAction implements ActionInterface, OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'compare';

    private $options = [
        'field'         => null,
        'compare_field' => null,
        'state'         => '',
        'skip_message'  => '',
        'then'          => [],
    ];

    public function apply(array $item): array
    {
        $field = $this->getOption('field');
        $compareField = $this->getOption('compare_field');
        $state = $this->getOption('state');
        $then = $this->getOption('then');
        $thenAction = $this->getThenAction($then, $item);

        if ($thenAction === null) {
            return $item;
        }

        $value = $item[$field] ?? null;
        $compareValue = $item[$compareField] ?? null;

        if (gettype($value) !== gettype($compareValue)) {
            return $item;
        }

        if ($state === 'EQUALS') {
            if (is_array($value) && is_array($compareValue)) {
                if (count($value) !== count($compareValue)) {
                    return $item;
                }

                sort($value);
                sort($compareValue);

                if ($value === $compareValue) {
                    return $thenAction->apply($item);
                }

                return $item;
            }

            if ($value === $compareValue) {
                return $thenAction->apply($item);
            }
        }

        if ($state === 'NOT_EQUALS') {
            if (is_array($value) && is_array($compareValue)) {
                if (count($value) !== count($compareValue)) {
                    return $thenAction->apply($item);
                }

                sort($value);
                sort($compareValue);

                if ($value !== $compareValue) {
                    return $thenAction->apply($item);
                }

                return $item;
            }

            if ($value !== $compareValue) {
                return $thenAction->apply($item);
            }
        }

        return $item;
    }

    private function getThenAction(
        array $then,
              $item
    ): CombineAction|SkipAction|CopyAction|DebugAction|RemoveAction|null {
        $action = null;
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
        }

        return $action;
    }
}