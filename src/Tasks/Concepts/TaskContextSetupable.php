<?php

namespace Magpie\Tasks\Concepts;

use Magpie\General\Concepts\Releasable;
use Magpie\Tasks\Context\TaskContext;
use Magpie\Tasks\Task;

/**
 * May setup task context
 */
interface TaskContextSetupable
{
    /**
     * Setup this part of task context
     * @param Task $parentTask Parent task
     * @param TaskContext $parentContext Parent context
     * @return iterable<Releasable>
     */
    public function setup(Task $parentTask, TaskContext $parentContext) : iterable;
}
