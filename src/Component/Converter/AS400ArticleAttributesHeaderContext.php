<?php

namespace Misery\Component\Converter;

use Misery\Component\Akeneo\Header\AkeneoHeader;

class AS400ArticleAttributesHeaderContext
{
    public function create(
        array $attributes,
        array $localizableCodes,
        array $scopableCodes,
        array $locales,
        array $currencies = ['EUR']
    ): AkeneoHeader {
        $header = new AkeneoHeader([
            'locale_codes' => $locales,
            'scope_codes' => ['ecommerce'],
            'currencies' => $currencies,
        ]);
        foreach ($attributes as $attributeCode => $attributeType) {
            $header->addValue($attributeCode, $attributeType);
            if (in_array($attributeCode, $localizableCodes)) {
                $header->addValue($attributeCode, $attributeType, ['has_locale' => true]);
            }
            if (in_array($attributeCode, $scopableCodes)) {
                $header->addValue($attributeCode, $attributeType, ['has_scope' => true]);
            }
        }

        return $header;
    }
}