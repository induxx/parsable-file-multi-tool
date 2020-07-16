<?php

namespace Misery\Component\Actions;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Reader\ItemReaderAwareInterface;
use Misery\Component\Reader\ItemReaderAwareTrait;

class ReplaceAction implements OptionsInterface, ItemReaderAwareInterface
{
    use OptionsTrait;
    use ItemReaderAwareTrait;
    private $repo;

    public const NAME = 'replace';

    /** @var array */
    private $options = [
        'method' => null,
        'source' => null,
        'key' => null,
    ];

    public function apply(array $item): array
    {
        if (isset($item[$this->options['key']])) {
            if ($this->options['method'] === 'getLabels') {
                $item[$this->options['key']] = $this->getLabels($item[$this->options['key']]);
            }
        }

        return $item;
    }

    // todo reference needs to come from source
    private function getLabels($reference)
    {
        return $this->getItem($reference)['label'][$this->options['locale']] ?? null;
    }

    private function getItem($reference)
    {
        return current($this->getReader()->find(['code' => $reference])->getItems());
    }
}