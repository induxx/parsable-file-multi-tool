<?php

namespace Misery\Component\Converter;

use Misery\Component\Akeneo\Header\AkeneoHeader;
use Misery\Component\Akeneo\Header\AkeneoHeaderTypes;
use Misery\Component\Akeneo\StandardValueCreator;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Pipeline\Exception\InvalidItemException;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Configurator\ConfigurationAwareInterface;
use Misery\Component\Configurator\ConfigurationTrait;
use Misery\Component\Modifier\ReferenceCodeModifier;
use Misery\Component\Modifier\StringToLowerModifier;

class AS400ArticleAttributesCsvToStructuredDataConverter implements ConverterInterface, OptionsInterface, RegisteredByNameInterface, ConfigurationAwareInterface
{
    use OptionsTrait;
    use ConfigurationTrait;

    /** @var AkeneoHeader */
    private $header;
    private $options = [
        'attributes:list' => null,
        'localizable_codes:list' => null,
        'akeneo-mapping:list' => null,
        'locales' => null,
    ];

    /** @var ProductStructuredDataToAkeneoApiConverter */
    private $reverter;
    /** @var StandardValueCreator */
    private $valueCreator;

    public function convert(array $itemCollection): array
    {
        if (null === $this->header) {

            // akeneo mapping filter
            $this->setOption('akeneo-mapping:list', array_map(function($listItem) {
                $path = pathinfo($listItem);
                if ($path['extension'] == 'standard') {
                    return $listItem;
                }
                return $path['extension'];
            }, $this->getOption('akeneo-mapping:list')));

            // header Factory
            $this->header = (new AS400ArticleAttributesHeaderContext())->create(
                $this->getOption('attributes:list'),
                $this->getOption('localizable_codes:list'),
                $this->getOption('locales')
            );

            $this->reverter = new ProductStructuredDataToAkeneoApiConverter();
            $this->reverter->setOption('attributes:list', $this->getOption('attributes:list'));
            $this->reverter->setOption('localizable_codes:list', $this->getOption('localizable_codes:list'));

            $this->valueCreator = new StandardValueCreator();
        }

        $akeneoMapping = $this->getOption('akeneo-mapping:list');

        $output = [];
        $invalid_msgs = [];
        foreach ($itemCollection as $item) {
            $context = $this->header->getContext($item['ATTRIBUTE_CODE']);
            $output['identifier'] = $item['SKU'];
            unset($item['SKU']);

            if (in_array($item['ATTRIBUTE_CODE'], ['5','174','281'])) {
                continue;
            }

            // clear up invalid values
            if (in_array($item['ATTRIBUTE_VALUE'], ['-', '--', '---', '----'])) {
                $item['ATTRIBUTE_VALUE'] = '';
            }

            if ($context['type'] === AkeneoHeaderTypes::SELECT || $context['type'] === AkeneoHeaderTypes::MULTISELECT) {
                $item['ATTRIBUTE_VALUE'] = $this->convertToSelectCode($item['ATTRIBUTE_CODE'], $item['ATTRIBUTE_VALUE']);
                // ASSUMPTION: the french value is always the dutch value with a french label
                $item['ATTRIBUTE_VALUE_FR'] = $item['ATTRIBUTE_VALUE'];
            }

            if ($context['type'] === AkeneoHeaderTypes::METRIC && $item['ATTRIBUTE_VALUE']) {
                $item['ATTRIBUTE_VALUE'] = str_replace(',', '.', $item['ATTRIBUTE_VALUE']);
                $item['ATTRIBUTE_VALUE_FR'] = str_replace(',', '.', $item['ATTRIBUTE_VALUE_FR']);

                if (strpos($item['ATTRIBUTE_VALUE'], '/') !== false) {
                    $value = $this->frac2dec($item['ATTRIBUTE_VALUE']);
                    if (is_numeric($value)) {
                        $item['ATTRIBUTE_VALUE'] = $value;
                    }
                }
                // metrics need a unit
                $unit = $akeneoMapping[$item['ATTRIBUTE_UNIT']] ?? null;
                if (null === $unit) {
                    $invalid_msgs[] = sprintf('metric code %s has no unit', $item['ATTRIBUTE_CODE']);
                }
                if (false === is_numeric($item['ATTRIBUTE_VALUE'])) {
                    $invalid_msgs[] = sprintf('metric code %s is not numeric', $item['ATTRIBUTE_CODE']);
                }
            }

            if ($context['type'] === AkeneoHeaderTypes::METRIC && $item['ATTRIBUTE_UNIT']) {
                $unit = $akeneoMapping[$item['ATTRIBUTE_UNIT']] ?? null;
                if (null === $unit) {
                    continue;
                }

                $output['values'][$item['ATTRIBUTE_CODE']][] = $this->valueCreator->createUnit($item['ATTRIBUTE_CODE'], $unit, $item['ATTRIBUTE_VALUE']);
                continue;
            }

            if ($context['type'] === AkeneoHeaderTypes::BOOLEAN && $item['ATTRIBUTE_UNIT']) {
                if ($item['ATTRIBUTE_UNIT'] === 'Ja') {
                    $item['ATTRIBUTE_UNIT'] = true;
                } elseif ($item['ATTRIBUTE_UNIT'] === 'Nee') {
                    $item['ATTRIBUTE_UNIT'] = false;
                } else {
                    $item['ATTRIBUTE_UNIT'] = '';
                }

                $output['values'][$item['ATTRIBUTE_CODE']][] = $this->valueCreator->create($item['ATTRIBUTE_CODE'], $item['ATTRIBUTE_UNIT']);
                continue;
            }

            if ($context['type'] === AkeneoHeaderTypes::PRICE && $item['ATTRIBUTE_UNIT']) {
                $output['values'][$item['ATTRIBUTE_CODE']][] = $this->valueCreator->createArrayData($item['ATTRIBUTE_CODE'], [
                    'amount' => $item['ATTRIBUTE_UNIT'],
                    'currency' => 'EUR',
                ]);
                continue;
            }

            if ($context['type'] === AkeneoHeaderTypes::MULTISELECT) {
                if ($context['has_locale'] === true) {
                    $output['values'][$item['ATTRIBUTE_CODE']][] = $this->valueCreator->createArrayData($item['ATTRIBUTE_CODE'], [$item['ATTRIBUTE_VALUE']], 'nl_BE');
                    $output['values'][$item['ATTRIBUTE_CODE']][] = $this->valueCreator->createArrayData($item['ATTRIBUTE_CODE'], [$item['ATTRIBUTE_VALUE_FR']], 'fr_BE');
                } elseif ($context['has_locale'] === false) {
                    $output['values'][$item['ATTRIBUTE_CODE']][] = $this->valueCreator->createArrayData($item['ATTRIBUTE_CODE'], [$item['ATTRIBUTE_VALUE']]);
                }
                continue;
            }

            if ($context['has_locale'] === true) {
                $output['values'][$item['ATTRIBUTE_CODE']][] = $this->valueCreator->create($item['ATTRIBUTE_CODE'], $item['ATTRIBUTE_VALUE'], 'nl_BE');
                $output['values'][$item['ATTRIBUTE_CODE']][] = $this->valueCreator->create($item['ATTRIBUTE_CODE'], $item['ATTRIBUTE_VALUE_FR'], 'fr_BE');
            } elseif ($context['has_locale'] === false) {
                $output['values'][$item['ATTRIBUTE_CODE']][] = $this->valueCreator->create($item['ATTRIBUTE_CODE'], $item['ATTRIBUTE_VALUE']);
            }
        }

        if (count($invalid_msgs) > 0) {
            throw new InvalidItemException(implode(', ', $invalid_msgs), $output);
        }

        return $output;
    }

    private function frac2dec(string $fraction)
    {
        list($whole, $fractional) = explode(' ', $fraction);

        $type = empty($fractional) ? 'improper' : 'mixed';

        list($numerator, $denominator) = explode('/', $type == 'improper' ? $whole : $fractional);

        $decimal = $numerator / ( 0 == $denominator ? 1 : $denominator );

        return $type == 'improper' ? $decimal : $whole + $decimal;
    }

    public function convertToSelectCode(string $code, string $value)
    {
        $refCode = new ReferenceCodeModifier();
        $strtolower = new StringToLowerModifier();

        return $strtolower->modify($refCode->modify($code.' '.$value));
    }

    public function revert(array $item): array
    {
        $item = $this->reverter->revert($item);

        return $item;
    }

    public function getName(): string
    {
        return 'as400/article-attributes';
    }
}