<?php

namespace Magpie\General\Randoms;

use Magpie\General\Traits\StaticClass;

/**
 * Common random character sets
 */
class RandomCharset
{
    use StaticClass;


    /**
     * Character set: numbers only
     */
    public const NUMBERS = '0123456789';
    /**
     * Lowercase alphabets and numbers only
     */
    public const LOWER_ALPHANUM = 'abcdefghijklmnopqrstuvwxyz0123456789';
    /**
     * Uppercase alphabets and numbers only
     */
    public const UPPER_ALPHANUM = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    /**
     * Uppercase/lowercase alphabets and numbers
     */
    public const FULL_ALPHANUM = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
}