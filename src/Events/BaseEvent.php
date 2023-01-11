<?php

namespace Magpie\Events;

use Magpie\Events\Concepts\Eventable;
use Magpie\Queues\BaseQueueRunnable;

/**
 * Basic implementation of event
 */
abstract class BaseEvent extends BaseQueueRunnable implements Eventable
{
    /**
     * @inheritDoc
     */
    protected final function onRun() : void
    {
        EventDelivery::deliver($this);
    }


    /**
     * @inheritDoc
     */
    public function getEventState() : mixed
    {
        return null;
    }
}