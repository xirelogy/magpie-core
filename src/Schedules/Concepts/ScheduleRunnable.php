<?php

namespace Magpie\Schedules\Concepts;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\TypeClassable;
use Magpie\System\Process\Process;

/**
 * May run for schedule
 */
interface ScheduleRunnable extends TypeClassable
{
    /**
     * Runnable description
     * @return string
     */
    public function getDesc() : string;


    /**
     * Create the process to be run
     * @return Process
     * @throws SafetyCommonException
     * @internal
     */
    public function _createProcess() : Process;
}