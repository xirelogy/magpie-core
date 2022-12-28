<?php

namespace Magpie\General\DateTimes;

use Magpie\General\Traits\StaticClass;

/**
 * System level timezones
 */
class SystemTimezone
{
    use StaticClass;


    /**
     * The default timezone for current application
     * @return string
     */
    public static function default() : string
    {
        return env('APP_TIMEZONE', 'UTC');
    }
}