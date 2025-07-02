<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Converter\Matcher;
use Misery\Component\Mapping\ColumnMapper;
use Misery\Model\DataStructure\ItemInterface;

class KeyMapperAction implements OptionsInterface, ActionItemInterface
{
    use OptionsTrait;

    private ColumnMapper $mapper;

    public const NAME = 'key_mapping';

    public function __construct()
    {
        $this->mapper = new ColumnMapper();
    }

    /** @var array */
    private $options = [
        'key' => null,
        'list' => [],
        'reverse' => false,
        'allow_join' => false,
        'separator' => '; ',
    ];

    public function applyAsItem(ItemInterface $item): void
    {
        $reverse = $this->getOption('reverse');
        $allowJoin = $this->getOption('allow_join');
        $separator = $this->getOption('separator');

        $list = array_filter($this->getOption('list'));

        if ($reverse) {
            $keys = [];
            foreach ($list as $match => $replacer) {
                if ($item->hasItem($replacer)) {
                    $item->copyItem($replacer, $match);
                    $keys[] = $replacer;
                }
            }
            foreach ($keys as $keyToUnset) {
                $item->removeItem($keyToUnset);
            }
            return;
        }

        if (!$allowJoin) {
            foreach ($list as $match => $replacer) {
                if ($item->hasItem($match)) {
                    $item->moveItem($match, $replacer);
                }
            }
        } else {
            $joined = [];
            foreach ($list as $match => $replacer) {
                if ($item->hasItem($match)) {
                    $value = $item->getItem($match);
                    if (!is_string($value)) {
                        // skip non-string values
                        $item->removeItem($match);
                        continue;
                    }
                    if (!isset($joined[$replacer])) {
                        $joined[$replacer] = [];
                    }
                    $joined[$replacer][] = $value;
                    $item->removeItem($match);
                }
            }
            foreach ($joined as $replacer => $values) {
                // Filter out empty strings to avoid multiple separators
                $filtered = array_filter($values, static fn($v) => $v !== '' && $v !== null);
                if ($filtered) {
                    $item->addItem($replacer, implode($separator, $filtered));
                }
            }
        }
    }

    public function apply(array $item): array
    {
        $reverse = $this->getOption('reverse');
        $allowJoin = $this->getOption('allow_join');
        $separator = $this->getOption('separator');
        if ($reverse) {
            $list = array_filter($this->getOption('list'));

            $keys = [];
            foreach ($list as $key => $value) {
                $newKey = $this->findMatchedValueData($item, $key) ?? $key;
                if (array_key_exists($value, $item)) {
                    $item[$newKey] = $item[$value];
                    $keys[] = $value;
                }
            }
            // Unset the collected keys
            foreach ($keys as $keyToUnset) {
                unset($item[$keyToUnset]);
            }
            return $item;
        }

        if ($key = $this->getOption('key')) {
            $item[$key] = $this->map($item[$key], $this->getOption('list'));
            return $item;
        }

        $list = array_filter($this->getOption('list'));
        // when dealing with converted data we need the primary keys
        // we just need to replace these keys
        $newList = [];
        foreach ($list as $key => $value) {
            $newKey = $this->findMatchedValueData($item, $key);
            // ig newKey is different from key, we need to replace it
            if ($newKey) {
                $this->replaceMatch($item, $newKey, $value);
            } else {
                $newKey = $key;
            }
            $newList[$newKey] = $value;
        }

        if ($allowJoin) {
            // join values for duplicate destination keys
            $joined = [];
            $toRemove = [];
            foreach ($newList as $src => $dst) {
                if (array_key_exists($src, $item)) {
                    $value = $item[$src];
                    if (!is_string($value)) {
                        // skip non-string values
                        $toRemove[] = $src;
                        continue;
                    }
                    if (!isset($joined[$dst])) {
                        $joined[$dst] = [];
                    }
                    $joined[$dst][] = $value;
                    $toRemove[] = $src;
                }
            }
            foreach ($toRemove as $src) {
                unset($item[$src]);
            }
            foreach ($joined as $dst => $values) {
                // Filter out empty strings to avoid multiple separators
                $filtered = array_filter($values, static fn($v) => $v !== '' && $v !== null);
                if ($filtered) {
                    $item[$dst] = implode($separator, $filtered);
                }
            }
            return $item;
        }

        if (count($newList) > 0) {
            return $this->map($item, $newList);
        }

        return $item;
    }

    private function map(array $item, array $list)
    {
        try {
            return $this->mapper->map($item, $list);
        } catch (\InvalidArgumentException) {
            return $item;
        }
    }

    private function replaceMatch(array &$item, string $match, string $to): void
    {
        $matcher = $item[$match]['matcher']->duplicateWithNewKey($to);
        $item[$matcher->getMainKey()] = $item[$match];
        $item[$matcher->getMainKey()]['matcher'] = $matcher;
        unset($item[$match]);
    }

    private function findMatchedValueData(array $item, string $field): int|string|null
    {
        foreach ($item as $key => $itemValue) {
            $matcher = $itemValue['matcher'] ?? null;
            /** @var $matcher Matcher */
            if ($matcher && $matcher->matches($field)) {
                return $key;
            }
        }

        return null;
    }
}
