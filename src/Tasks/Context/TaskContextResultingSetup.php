<?php

namespace Magpie\Tasks\Context;

use Magpie\Tasks\Concepts\TaskContextSetupable;
use Magpie\Tasks\FailedTaskResult;
use Magpie\Tasks\SuccessTaskResult;
use Magpie\Tasks\Task;
use Throwable;

/**
 * May setup task context on how to handle task running result
 */
abstract class TaskContextResultingSetup implements TaskContextSetupable
{
    /**
     * @inheritDoc
     */
    public final function setup(Task $parentTask, TaskContext $parentContext) : iterable
    {
        return [];
    }


    /**
     * Handle successfully run task
     * @param Task $task
     * @param T $result
     * @return SuccessTaskResult<T>|null
     * @template T
     */
    public function onRunTaskSuccess(Task $task, mixed $result) : ?SuccessTaskResult
    {
        _used($task, $result);
        return null;
    }


    /**
     * Handle failed run task
     * @param Task $task
     * @param Throwable $ex
     * @return FailedTaskResult|null
     */
    public function onRunTaskFailed(Task $task, Throwable $ex) : ?FailedTaskResult
    {
        _used($task, $ex);
        return null;
    }
}