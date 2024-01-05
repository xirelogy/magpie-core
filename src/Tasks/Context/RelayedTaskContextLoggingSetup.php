<?php

namespace Magpie\Tasks\Context;

use Magpie\Logs\Concepts\Loggable;
use Magpie\Logs\Loggers\DefaultLogger;
use Magpie\Logs\LogRelay;
use Magpie\Tasks\Task;

/**
 * May setup task context on redirecting all logging to specific relay
 */
class RelayedTaskContextLoggingSetup extends TaskContextLoggingSetup
{
    /**
     * @var LogRelay Specific relay
     */
    protected readonly LogRelay $relay;


    /**
     * Constructor
     * @param LogRelay $relay
     */
    protected function __construct(LogRelay $relay)
    {
        $this->relay = $relay;
    }


    /**
     * @inheritDoc
     */
    protected function createLogger(Task $parentTask, TaskContext $parentContext) : Loggable
    {
        return new DefaultLogger($this->relay);
    }


    /**
     * Create an instance
     * @param LogRelay $relay
     * @return static
     */
    public static function create(LogRelay $relay) : static
    {
        return new static($relay);
    }
}