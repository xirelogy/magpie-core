<?php

namespace Magpie\Codecs\Impls;

use Magpie\Exceptions\ParseFailedException;

/**
 * Support for range check for numerical values
 * @template T
 */
class NumericRangeCheck
{
    /**
     * Check the value to be within given specified range
     * @param T $value
     * @param T|null $min
     * @param T|null $max
     * @return void
     * @throws ParseFailedException
     */
    public static function checkRange(int|float $value, int|float|null $min, int|float|null $max) : void
    {
        if ($min !== null && $value < $min) {
            throw new ParseFailedException(_format_safe(_l('Must not be less than {{0}}'), $min) ?? '');
        }
        if ($max !== null && $value > $max) {
            throw new ParseFailedException(_format_safe(_l('Must not be more than {{0}}'), $max) ?? '');
        }
    }
}