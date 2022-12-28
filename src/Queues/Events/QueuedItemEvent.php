<?php

namespace Magpie\Queues\Events;

use Magpie\Events\BaseEvent;
use Magpie\Queues\PendingExecutable;

/**
 * Event related to a queued item
 */
abstract class QueuedItemEvent extends BaseEvent
{
    /**
     * @var PendingExecutable Associated job
     */
    protected readonly PendingExecutable $job;


    /**
     * Constructor
     * @param PendingExecutable $job
     */
    protected function __construct(PendingExecutable $job)
    {
        $this->job = $job;
    }


    /**
     * @inheritDoc
     */
    public function getEventState() : PendingExecutable
    {
        return $this->job;
    }


    /**
     * Create a new event
     * @param PendingExecutable $job
     * @return static
     */
    public static function create(PendingExecutable $job) : static
    {
        return new static($job);
    }
}