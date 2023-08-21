<?php

namespace Misery\Component\Converter;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Common\Registry\Registry;
use Misery\Component\Decoder\ItemDecoderFactory;
use Misery\Component\Encoder\ItemEncoderFactory;
use Misery\Component\Format\ArrayListFormat;
use Misery\Component\Format\StringToBooleanFormat;
use Misery\Component\Format\ArrayGroupFormat;
use Misery\Component\Format\StringToIntFormat;
use Misery\Component\Format\StringToListFormat;
use Misery\Component\Modifier\ArrayUnflattenModifier;
use Misery\Component\Modifier\NullifyEmptyStringModifier;

class AkeneoAttributeCsvConverter implements ConverterInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private $options = [
        'properties' => [
            'unique' => [
                'boolean' => null,
            ],
            'useable_as_grid_filter' => [
                'boolean' => null,
            ],
            'localizable' => [
                'boolean' => null,
            ],
            'scopable' => [
                'boolean' => null,
            ],
            'is_read_only' => [
                'boolean' => null,
            ],
            'allowed_extensions' => [
                'list' => null,
            ],
            'sort_order' => [
                'integer' => null,
            ],
            'available_locales' => [
                'list' => null,
            ],
            'guidelines' => [
                'list' => null,
            ],
            'labels' => [
                'group' => null,
            ],
            'group_labels' => [
                'group' => null,
            ],
        ],
        'parse' => [
            'nullify' => null,
        ]
    ];
    private $decoder;
    private $encoder;

    public function convert(array $item): array
    {
        if (null === $this->encoder) {
            $this->encoder = $this->ItemEncoderFactory()->createItemEncoder([
                'encode' => $this->getOption('properties'),
                'parse' => $this->getOption('parse'),
            ]);
        }

        $item = $this->encoder->encode($item);

        return $item;
    }

    public function revert(array $item): array
    {
        if (null === $this->decoder) {
            $this->decoder = $this->ItemDecoderFactory()->createItemDecoder([
                'encode' => $this->getOption('properties'),
                'parse' => $this->getOption('parse'),
            ]);
        }

        $item = $this->decoder->decode($item);

        return  $item;
    }

    private function ItemEncoderFactory(): ItemEncoderFactory
    {
        $encoderFactory = new ItemEncoderFactory();

        $formatRegistry = new Registry('format');
        $formatRegistry
            ->register(ArrayGroupFormat::NAME, new ArrayGroupFormat())
            ->register(StringToListFormat::NAME, new StringToListFormat())
            ->register(StringToIntFormat::NAME, new StringToIntFormat())
            ->register(StringToBooleanFormat::NAME, new StringToBooleanFormat())
            ->register(ArrayListFormat::NAME, new ArrayListFormat())
        ;
        $modifierRegistry = new Registry('modifier');
        $modifierRegistry
            ->register(ArrayUnflattenModifier::NAME, new ArrayUnflattenModifier())
            ->register(NullifyEmptyStringModifier::NAME, new NullifyEmptyStringModifier())
        ;

        $encoderFactory->addRegistry($modifierRegistry);
        $encoderFactory->addRegistry($formatRegistry);

        return $encoderFactory;
    }

    private function ItemDecoderFactory(): ItemDecoderFactory
    {
        $encoderFactory = new ItemDecoderFactory();

        $formatRegistry = new Registry('format');
        $formatRegistry
            ->register(ArrayGroupFormat::NAME, new ArrayGroupFormat())
            ->register(StringToListFormat::NAME, new StringToListFormat())
            ->register(StringToIntFormat::NAME, new StringToIntFormat())
            ->register(StringToBooleanFormat::NAME, new StringToBooleanFormat())
            ->register(ArrayListFormat::NAME, new ArrayListFormat())
        ;
        $modifierRegistry = new Registry('modifier');
        $modifierRegistry
            ->register(NullifyEmptyStringModifier::NAME, new NullifyEmptyStringModifier())
            ->register(ArrayUnflattenModifier::NAME, new ArrayUnflattenModifier())
        ;

        $encoderFactory->addRegistry($modifierRegistry);
        $encoderFactory->addRegistry($formatRegistry);

        return $encoderFactory;
    }

    public function getName(): string
    {
        return 'akeneo/attribute/csv';
    }
}