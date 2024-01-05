<?php

namespace Magpie\Tasks;

use Magpie\Locales\Concepts\Localizable;
use Magpie\Tasks\Context\TaskContext;
use Throwable;

/**
 * A task object wraps something to be done into a single clear unit of execution. The input
 * and output of the task is expected to be localized as the task member
 * @template T
 */
abstract class Task
{
    /**
     * The task name
     * @return Localizable|string
     */
    public function getName() : Localizable|string
    {
        return static::class;
    }


    /**
     * The task description (if any)
     * @return Localizable|string|null
     */
    public function getDescription() : Localizable|string|null
    {
        return null;
    }


    /**
     * The task input for description
     * @return object
     */
    public function getInput() : object
    {
        return obj();
    }


    /**
     * Run the task from given context
     * @param TaskContext $context
     * @return mixed
     * @throws Throwable
     * @internal
     */
    public final function _runFromContext(TaskContext $context) : mixed
    {
        _used($context);

        return $this->onRun();
    }


    /**
     * Run the task
     * @return T
     * @throws Throwable
     */
    protected abstract function onRun() : mixed;
}