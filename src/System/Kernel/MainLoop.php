<?php

namespace Magpie\System\Kernel;

use Magpie\General\Traits\StaticClass;
use Magpie\System\Concepts\MainLoopPollable;
use Magpie\System\Impls\MainLoopInstance;

/**
 * Main loop implementation to poll, process and dispatch messages to support
 * asynchronous execution
 */
final class MainLoop
{
    use StaticClass;

    /**
     * Top priority
     */
    public const PRIORITY_TOP = 0;
    /**
     * Time sensitive priority
     */
    public const PRIORITY_TIME = 1;
    /**
     * I/O priority
     */
    public const PRIORITY_IO = 8;
    /**
     * Almost idle priority
     */
    public const PRIORITY_IDLE = 10;


    /**
     * Register a poll to the loop
     * @param MainLoopPollable $poll
     * @return bool
     */
    public static function registerPoll(MainLoopPollable $poll) : bool
    {
        return MainLoopInstance::instance()->registerPoll($poll);
    }


    /**
     * Deregister a poll from the loop
     * @param MainLoopPollable $poll
     * @return bool
     */
    public static function deregisterPoll(MainLoopPollable $poll) : bool
    {
        return MainLoopInstance::instance()->deregisterPoll($poll);
    }


    /**
     * Run the main loop until exit condition is fulfilled (no more polls, or
     * exit signal is received)
     * @return mixed
     */
    public static function run() : mixed
    {
        return MainLoopInstance::instance()->run();
    }
}