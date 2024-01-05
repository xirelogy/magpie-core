<?php

namespace Magpie\Tasks\Context;

use Magpie\Tasks\Concepts\TaskContextSetupable;
use Magpie\Tasks\Task;

/**
 * May setup task context on how to handle preparation
 */
abstract class TaskContextPreparingSetup implements TaskContextSetupable
{
    /**
     * @inheritDoc
     */
    public final function setup(Task $parentTask, TaskContext $parentContext) : iterable
    {
        $this->onPrepareTask($parentTask, $parentContext);

        return [];
    }


    /**
     * Handle task preparation
     * @param Task $parentTask
     * @param TaskContext $parentContext
     * @return void
     */
    protected abstract function onPrepareTask(Task $parentTask, TaskContext $parentContext) : void;
}