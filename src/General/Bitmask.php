<?php

namespace Magpie\General;

use Magpie\General\Traits\StaticClass;

/**
 * Bitmask related support
 */
class Bitmask
{
    use StaticClass;


    /**
     * Check if value has target flag set
     * @param int $value Value to be checked
     * @param int $flag Target flag to be checked
     * @return bool
     */
    public static function isSet(int $value, int $flag) : bool
    {
        return ($value & $flag) === $flag;
    }
}