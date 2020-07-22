<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Reader\ItemReaderAwareInterface;
use Misery\Component\Reader\ItemReaderAwareTrait;

// TODO we are stuck on 'code' source -> getReference or SourceRepository in favor of reader are other options

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
            if ($this->options['method'] === 'getLabel') {
                $item[$this->options['key']] = $this->getLabel($item[$this->options['key']], $this->options['locale']);
            }
        }

        return $item;
    }

    // todo reference needs to come from source
    private function getLabel($reference, $locale)
    {
        return $this->getItem($reference)['label'][$locale] ?? null;
    }

    private function getItem($reference)
    {
        return current($this->getReader()->find(['code' => $reference])->getItems());
    }
}