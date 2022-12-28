<?php

namespace Magpie\Schedules\Concepts;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Schedules\Constants\ScheduleDayOfMonth;
use Magpie\Schedules\Constants\ScheduleDayOfWeek;

/**
 * May define schedule
 */
interface ScheduleDefinable
{
    /**
     * To be scheduled every minute
     * @return $this
     * @throws SafetyCommonException
     */
    public function everyMinute() : static;


    /**
     * To be scheduled every 2 minutes
     * @return $this
     * @throws SafetyCommonException
     */
    public function everyTwoMinutes() : static;


    /**
     * To be scheduled every 3 minutes
     * @return $this
     * @throws SafetyCommonException
     */
    public function everyThreeMinutes() : static;


    /**
     * To be scheduled every 4 minutes
     * @return $this
     * @throws SafetyCommonException
     */
    public function everyFourMinutes() : static;


    /**
     * To be scheduled every 5 minutes
     * @return $this
     * @throws SafetyCommonException
     */
    public function everyFiveMinutes() : static;


    /**
     * To be scheduled every 6 minutes
     * @return $this
     * @throws SafetyCommonException
     */
    public function everySixMinutes() : static;


    /**
     * To be scheduled every 10 minutes
     * @return $this
     * @throws SafetyCommonException
     */
    public function everyTenMinutes() : static;


    /**
     * To be scheduled every 12 minutes
     * @return $this
     * @throws SafetyCommonException
     */
    public function everyTwelveMinutes() : static;


    /**
     * To be scheduled every 15 minutes
     * @return $this
     * @throws SafetyCommonException
     */
    public function everyFifteenMinutes() : static;


    /**
     * To be scheduled every 20 minutes
     * @return $this
     * @throws SafetyCommonException
     */
    public function everyTwentyMinutes() : static;


    /**
     * To be scheduled every 30 minutes
     * @return $this
     * @throws SafetyCommonException
     */
    public function everyThirtyMinutes() : static;


    /**
     * To be scheduled every hour at given minutes
     * @param int ...$minutes
     * @return $this
     * @throws SafetyCommonException
     */
    public function hourlyAt(int ...$minutes) : static;


    /**
     * To be scheduled every hour
     * @return $this
     * @throws SafetyCommonException
     */
    public function everyHour() : static;


    /**
     * To be scheduled every 2 hours
     * @return $this
     * @throws SafetyCommonException
     */
    public function everyTwoHours() : static;


    /**
     * To be scheduled every 3 hours
     * @return $this
     * @throws SafetyCommonException
     */
    public function everyThreeHours() : static;


    /**
     * To be scheduled every 4 hours
     * @return $this
     * @throws SafetyCommonException
     */
    public function everyFourHours() : static;


    /**
     * To be scheduled every 6 hours
     * @return $this
     * @throws SafetyCommonException
     */
    public function everySixHours() : static;


    /**
     * To be scheduled daily at given time (HH:MM)
     * @param string $time
     * @return $this
     * @throws SafetyCommonException
     */
    public function dailyAt(string $time) : static;


    /**
     * To be scheduled every day at given hours
     * @param int ...$hours
     * @return $this
     * @throws SafetyCommonException
     */
    public function dailyAtHours(int ...$hours) : static;


    /**
     * To be scheduled on weekdays
     * @return $this
     * @throws SafetyCommonException
     */
    public function onWeekdays() : static;


    /**
     * To be scheduled on weekdays
     * @return $this
     * @throws SafetyCommonException
     */
    public function onWeekends() : static;


    /**
     * To be scheduled on specific day(s) of week
     * @param ScheduleDayOfWeek ...$days
     * @return $this
     * @throws SafetyCommonException
     */
    public function onDayOfWeek(ScheduleDayOfWeek ...$days) : static;


    /**
     * To be scheduled on specific day(s) of month
     * @param int|ScheduleDayOfMonth ...$days
     * @return $this
     * @throws SafetyCommonException
     */
    public function onDayOfMonth(int|ScheduleDayOfMonth ...$days) : static;


    /**
     * To be scheduled on specific month(s)
     * @param int ...$months
     * @return $this
     * @throws SafetyCommonException
     */
    public function onMonth(int ...$months) : static;


    /**
     * To be scheduled according to given CRON expression
     * @param string $expression
     * @return $this
     * @throws SafetyCommonException
     */
    public function withCron(string $expression) : static;


    /**
     * To be scheduled in given timezone
     * @param string $timezone
     * @return $this
     */
    public function withTimezone(string $timezone) : static;


    /**
     * Specify if running in background
     * @param bool $isRunInBackground
     * @return $this
     */
    public function withBackgroundRunning(bool $isRunInBackground = true) : static;
}