<?php

namespace Magpie\Events\Traits;

use Exception;
use Magpie\Events\EventDelivery;
use Magpie\Events\EventQueueRunnable;
use Magpie\Facades\Log;
use Magpie\Queues\Concepts\Queueable;

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
            Log::warning($ex->getMessage());
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