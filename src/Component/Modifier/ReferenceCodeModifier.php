<?php

namespace Misery\Component\Modifier;

use Misery\Component\Common\Modifier\CellModifier;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

/**
 * Class ReferenceCodeModifier
 * @package Misery\Component\Modifier
 *
 * value response may contain only letters, numbers and underscores
 * value is unrecoverable after modification
 */
class ReferenceCodeModifier implements CellModifier, OptionsInterface
{
    public const NAME = 'reference_code';
    use OptionsTrait;

    private $options = [
        'old_pattern' => '/[^a-zA-Z0-9_]/',
        'new_pattern' => '/[^a-zA-Z0-9]+/',
        'use_pattern' => 'old_pattern',
    ];

    /**
     *
     * @param string $value
     * @return string
     */
    public function modify(string $value): string
    {
        $pattern = $this->getOption($this->getOption('use_pattern'));
        $delimiter = '_';

        // only do the heavier work if there's something to normalize…
        if (false === ctype_lower($value)) {
            // 1) Transliterate any UTF-8 letters into plain ASCII (ï → i, ö → o, etc.)
            //    iconv is a C-library call and very fast.
            $ascii = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
            if (false !== $ascii) {
                $value = $ascii;
            }

            // 3) Collapse any run of non-[a-z0-9] into a single delimiter
            //    (we drop underscores from the “keep” list so that we don’t end up
            //     preserving stray _’s in the input)
            $value = preg_replace($pattern, $delimiter, $value);

            // 4) Trim any leading/trailing delimiters
            $value = trim($value, $delimiter);
        }

        return $value;
    }
}