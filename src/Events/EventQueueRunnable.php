<?php

namespace Magpie\Events;

use Magpie\Queues\BaseQueueRunnable;

/**
 * Allow event to be dispatched to queue
 */
class EventQueueRunnable extends BaseQueueRunnable
{
    /**
     * @var BaseEvent Associated event
     */
    public readonly BaseEvent $event;


    /**
     * Constructor
     * @param BaseEvent $event
     */
    public function __construct(BaseEvent $event)
    {
        $this->event = $event;
    }


    /**
     * @inheritDoc
     */
    protected function onRun() : void
    {
        $this->event->run();
    }
}