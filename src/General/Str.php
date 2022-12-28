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
     * @return string|null
     */
    public static function trimWithEmptyAsNull(?string $value) : ?string
    {
        if ($value === null) return null;

        $value = trim($value);
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
        return preg_match('/^(0|-?[1-9][0-9]*)$/', $text) === 1;
    }
}