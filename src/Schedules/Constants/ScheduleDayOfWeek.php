<?php

namespace Magpie\Schedules\Constants;

/**
 * Day of week for scheduling purpose
 */
enum ScheduleDayOfWeek : int
{
    /**
     * Sunday
     */
    case SUNDAY = 0;
    /**
     * Monday
     */
    case MONDAY = 1;
    /**
     * Tuesday
     */
    case TUESDAY = 2;
    /**
     * Wednesday
     */
    case WEDNESDAY = 3;
    /**
     * Thursday
     */
    case THURSDAY = 4;
    /**
     * Friday
     */
    case FRIDAY = 5;
    /**
     * Saturday
     */
    case SATURDAY = 6;
}