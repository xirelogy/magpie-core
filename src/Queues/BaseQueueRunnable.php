<?php

namespace Magpie\Queues;

use Magpie\Queues\Concepts\Queueable;
use Magpie\Queues\Concepts\QueueRunnable;

/**
 * Common implementation for runnable that can be dispatched to queue
 */
abstract class BaseQueueRunnable implements QueueRunnable
{
    /**
     * @inheritDoc
     */
    public function queueDispatch() : Queueable
    {
        return new PendingQueueable($this);
    }
}