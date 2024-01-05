<?php

namespace Magpie\Tasks\Context;

use Exception;
use Magpie\General\Concepts\Releasable;
use Magpie\System\Kernel\ExceptionHandler;
use Magpie\Tasks\Concepts\TaskContextSetupable;
use Magpie\Tasks\FailedTaskResult;
use Magpie\Tasks\SuccessTaskResult;
use Magpie\Tasks\Task;
use Magpie\Tasks\TaskResult;
use Throwable;

/**
 * Context of task
 */
abstract class TaskContext
{
    /**
     * @var array<Releasable> Handles to be released upon context finishes
     */
    private array $releaseHandles = [];


    /**
     * Constructor
     */
    protected function __construct()
    {

    }


    /**
     * @param Task<T> $task
     * @return TaskResult<T>
     * @template T
     */
    public final function run(Task $task) : TaskResult
    {
        // Setup the context (no exception allowed)
        try {
            $this->onSetup($task);
        } catch (Throwable $ex) {
            ExceptionHandler::systemCritical($ex);
        }

        // Run the task
        try {
            $ret = $task->_runFromContext($this);
            return $this->onRunTaskSuccess($task, $ret);
        } catch (Throwable $ex) {
            return $this->onRunTaskFailed($task, $ex);
        } finally {
            $this->releaseSetup();
        }
    }


    /**
     * Setup the task context
     * @param Task $task
     * @return void
     * @throws Exception
     */
    protected abstract function onSetup(Task $task) : void;


    /**
     * Run a setup and queue the release handles accordingly
     * @param Task $task
     * @param TaskContextSetupable|null $setup
     * @return void
     */
    protected function runSetup(Task $task, ?TaskContextSetupable $setup) : void
    {
        if ($setup === null) return;

        foreach ($setup->setup($task, $this) as $releaseHandle) {
            // Release handles are FILO
            array_unshift($this->releaseHandles, $releaseHandle);
        }
    }


    /**
     * Handle successfully run task
     * @param Task $task
     * @param T $result
     * @return SuccessTaskResult<T>
     * @template T
     */
    protected function onRunTaskSuccess(Task $task, mixed $result) : SuccessTaskResult
    {
        return new SuccessTaskResult($result);
    }


    /**
     * Handle failed run task
     * @param Task $task
     * @param Throwable $ex
     * @return FailedTaskResult
     */
    protected function onRunTaskFailed(Task $task, Throwable $ex) : FailedTaskResult
    {
        return new FailedTaskResult($ex);
    }


    /**
     * Release the setup context
     * @return void
     */
    private function releaseSetup() : void
    {
        foreach ($this->releaseHandles as $releaseHandle) {
            $releaseHandle->release();
        }

        $this->releaseHandles = [];
    }
}