<?php

namespace Magpie\Commands\Concepts;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Schedules\CommandScheduleDefinition;

/**
 * May schedule the current command
 */
interface CommandSchedulable
{
    /**
     * All schedules
     * @return iterable<CommandScheduleDefinition>
     * @throws SafetyCommonException
     */
    public static function getSchedules() : iterable;
}