<?php

namespace Magpie\Tasks\Context;

use Magpie\Logs\Concepts\Loggable;
use Magpie\System\Kernel\Kernel;
use Magpie\Tasks\Concepts\TaskContextSetupable;
use Magpie\Tasks\Task;

/**
 * May setup task context on how to handle logging
 */
abstract class TaskContextLoggingSetup implements TaskContextSetupable
{
    /**
     * @inheritDoc
     */
    public final function setup(Task $parentTask, TaskContext $parentContext) : iterable
    {
        yield Kernel::current()->scopeLogger($this->createLogger($parentTask, $parentContext));
    }


    /**
     * Create the specific logger interface to be used within this context setup
     * @param Task $parentTask`
     * @param TaskContext $parentContext
     * @return Loggable
     */
    protected abstract function createLogger(Task $parentTask, TaskContext $parentContext) : Loggable;
}