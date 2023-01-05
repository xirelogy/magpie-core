<?php

namespace Magpie\Events\Traits;

use Exception;
use Magpie\Events\EventDelivery;
use Magpie\Events\EventQueueRunnable;
use Magpie\Queues\Concepts\Queueable;
use Magpie\System\Kernel\ExceptionHandler;

/**
 * May run for event
 * @requires \Magpie\Events\Concepts\Eventable
 */
trait EventRunnable
{
    /**
     * Start running
     * @return void
     * @throws Exception
     */
    public final function run() : void
    {
        EventDelivery::deliver($this);
    }


    /**
     * Start running, ignoring any exceptions
     * @return void
     */
    public final function safeRun() : void
    {
        try {
            $this->run();
        } catch (Exception $ex) {
            ExceptionHandler::ignoredAndWarn(static::class, 'run()', $ex);
        }
    }


    /**
     * Dispatch to queue
     * @return Queueable
     */
    public final function queueDispatch() : Queueable
    {
        $target = new EventQueueRunnable($this);
        return $target->queueDispatch();
    }
}