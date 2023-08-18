<?php

namespace Magpie\General;

use Magpie\General\Traits\StaticClass;

/**
 * String utilities
 */
class Str
{
    use StaticClass;


    /**
     * Trim the string and treat empty string as null
     * @param string|null $value
     * @param string $characters
     * @return string|null
     */
    public static function trimWithEmptyAsNull(?string $value, string $characters = " \t\n\r\0\x0B") : ?string
    {
        if ($value === null) return null;

        $value = trim($value, $characters);
        return $value !== '' ? $value : null;
    }


    /**
     * If string is null or empty
     * @param string|null $value
     * @return bool
     */
    public static function isNullOrEmpty(?string $value) : bool
    {
        if ($value === null) return true;
        if ($value === '') return true;

        return false;
    }


    /**
     * If given text is a valid integer
     * @param string $text
     * @return bool
     */
    public static function isInteger(string $text) : bool
    {
        return preg_match('/^(0|-?[1-9]\d*)$/', $text) === 1;
    }
}