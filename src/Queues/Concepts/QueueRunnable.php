<?php

namespace Magpie\Queues\Concepts;

use Magpie\General\Concepts\Runnable;

/**
 * Runnable that can be dispatched to queue (dispatch for enqueueing)
 */
interface QueueRunnable extends Runnable
{
    /**
     * Dispatch to queue
     * @return Queueable
     */
    public function queueDispatch() : Queueable;
}