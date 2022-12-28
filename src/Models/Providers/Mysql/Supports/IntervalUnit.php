<?php

namespace Magpie\Models\Providers\Mysql\Supports;

/**
 * Temporal interval unit
 */
enum IntervalUnit : string
{
    /**
     * Microseconds
     */
    case MICROSECOND = 'microsecond';
    /**
     * Seconds
     */
    case SECOND = 'second';
    /**
     * Minutes
     */
    case MINUTE = 'minute';
    /**
     * Hours
     */
    case HOUR = 'hour';
    /**
     * Days
     */
    case DAY = 'day';
    /**
     * Weeks
     */
    case WEEK = 'week';
    /**
     * Months
     */
    case MONTH = 'month';
    /**
     * Quarters
     */
    case QUARTER = 'quarter';
    /**
     * Years
     */
    case YEAR = 'year';
}