<?php
declare(strict_types=1);

namespace Misery\Component\Converter\Akeneo\Api;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Converter\ConverterInterface;

class AttributeOptions implements ConverterInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private $options = [
    ];

    public function convert(array $item): array
    {
        $item = CatalogLabelConverter::convert($item);

        return $item;
    }

    public function revert(array $item): array
    {
        $item = CatalogLabelConverter::revert($item);

        if (isset($item['sort_order']) && is_string($item['sort_order'])) {
            $item['sort_order'] = (int) $item['sort_order'];
        }

        return $item;
    }

    public function getName(): string
    {
        return 'akeneo/options/api';
    }
}
