<?php
declare(strict_types=1);

namespace Misery\Component\Converter\Akeneo\Api;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Converter\ConverterInterface;

class Family implements ConverterInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private $options = [
        'attribute_as_label' => null,
    ];

    public function convert(array $item): array
    {
        $item = CatalogLabelConverter::convert($item);

        return $item;
    }

    public function revert(array $item): array
    {
        $attrAsLabel = $this->getOption('attribute_as_label');

        $item = CatalogLabelConverter::revert($item);

        if (isset($item['requirements-ecommerce'])) {
            $item['attribute_requirements']['ecommerce'] = $item['requirements-ecommerce'];
            unset($item['requirements-ecommerce']);
        }

        if (empty($item['attribute_as_label'])) {
            unset($item['attribute_as_label']);
        }
        if ($attrAsLabel) {
            $item['attribute_as_label'] = $attrAsLabel;
        }
        if (empty($item['attribute_as_image'])) {
            unset($item['attribute_as_image']);
        }

        return $item;
    }

    public function getName(): string
    {
        return 'akeneo/family/api';
    }
}
