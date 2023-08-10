<?php

namespace Misery\Component\AttributeFormatter;

class NumberAttributeFormatter implements PropertyFormatterInterface
{
    private $thousandsSep;
    private $decimalPoint;
    private $decimal;

    public function __construct(int $decimal = 2, string $decimalPoint = '.', string $thousandsSep = ',')
    {
        $this->decimal = $decimal;
        $this->decimalPoint = $decimalPoint;
        $this->thousandsSep = $thousandsSep;
    }

    public function format($value, array $context = [])
    {
        $decimal = (int) ($context['decimals-to-use'] ?? $this->decimal);
        if (isset($context['decimal-attributes-to-format'], $context['decimals-to-use']) && !in_array($context['current-attribute-code'], explode(',', $context['decimal-attributes-to-format']), true)) {
            $decimal = $this->decimal;
        }

        if (is_array($value) && array_key_exists('amount', $value)){
            if ($value['amount'] === null) {
                return null;
            }

            $value['amount'] = str_replace(
                $this->decimalPoint.sprintf('%-0'.$decimal.'s', ''),
                '',
                number_format($value['amount'], $decimal, $this->decimalPoint, $this->thousandsSep)
            );

            return $value;
        }

        return str_replace(
            $this->decimalPoint.sprintf('%-0'.$decimal.'s', ''),
            '',
            number_format($value, $decimal, $this->decimalPoint, $this->thousandsSep)
        );
    }

    public function supports(string $type): bool
    {
        return false;
        return in_array($type, ['pim_catalog_number', 'pim_catalog_metric']);
    }
}