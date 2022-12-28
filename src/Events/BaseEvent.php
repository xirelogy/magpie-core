<?php

namespace Magpie\Events;

use Magpie\Events\Concepts\Eventable;
use Magpie\Events\Traits\EventRunnable;

/**
 * Basic implementation of event
 */
abstract class BaseEvent implements Eventable
{
    use EventRunnable;


    /**
     * @inheritDoc
     */
    public function getEventState() : mixed
    {
        return null;
    }
}