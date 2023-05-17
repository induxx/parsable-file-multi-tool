<?php

namespace Misery\Component\Converter;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;

class AkeneoAttributeApiConverter implements ConverterInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private $options = [];

    public function convert(array $item): array
    {
        return $item;
    }

    public function revert(array $item): array
    {
        return $item;
    }

    public function getName(): string
    {
        return 'akeneo/attribute/api';
    }
}