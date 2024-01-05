<?php

namespace Magpie\Tasks\Context;

use Magpie\Tasks\Concepts\TaskContextSetupable;
use Magpie\Tasks\FailedTaskResult;
use Magpie\Tasks\SuccessTaskResult;
use Magpie\Tasks\Task;
use Throwable;

class CommonTaskContext extends TaskContext
{
    /**
     * @var TaskContextLoggingSetup|null Logging related setup
     */
    protected ?TaskContextLoggingSetup $loggingSetup;
    /**
     * @var TaskContextPreparingSetup|null Preparation setup
     */
    protected ?TaskContextPreparingSetup $preparingSetup;
    /**
     * @var TaskContextResultingSetup|null Result handling related setup
     */
    protected ?TaskContextResultingSetup $resultingSetup;


    /**
     * Constructor
     */
    protected function __construct(?TaskContextLoggingSetup $loggingSetup, ?TaskContextPreparingSetup $preparingSetup, ?TaskContextResultingSetup $resultingSetup)
    {
        parent::__construct();

        $this->loggingSetup = $loggingSetup;
        $this->preparingSetup = $preparingSetup;
        $this->resultingSetup = $resultingSetup;
    }


    /**
     * @inheritDoc
     */
    protected function onSetup(Task $task) : void
    {
        $this->runSetup($task, $this->loggingSetup);
        $this->runSetup($task, $this->preparingSetup);
        $this->runSetup($task, $this->resultingSetup);
    }


    /**
     * @inheritDoc
     */
    protected function onRunTaskSuccess(Task $task, mixed $result) : SuccessTaskResult
    {
        $tryResult = $this->resultingSetup?->onRunTaskSuccess($task, $result);
        if ($tryResult !== null) return $tryResult;

        return parent::onRunTaskSuccess($task, $result);
    }


    /**
     * @inheritDoc
     */
    protected function onRunTaskFailed(Task $task, Throwable $ex) : FailedTaskResult
    {
        $tryResult = $this->resultingSetup?->onRunTaskFailed($task, $ex);
        if ($tryResult !== null) return $tryResult;

        return parent::onRunTaskFailed($task, $ex);
    }


    /**
     * Create a context instance
     * @param iterable<TaskContextSetupable> $setups
     * @return static
     */
    public static function create(iterable $setups) : static
    {
        $loggingSetup = null;
        $preparingSetup = null;
        $resultingSetup = null;

        foreach ($setups as $setup) {
            if ($setup instanceof TaskContextLoggingSetup) $loggingSetup = $setup;
            if ($setup instanceof TaskContextPreparingSetup) $preparingSetup = $setup;
            if ($setup instanceof TaskContextResultingSetup) $resultingSetup = $setup;
        }

        return new static($loggingSetup, $preparingSetup, $resultingSetup);
    }
}