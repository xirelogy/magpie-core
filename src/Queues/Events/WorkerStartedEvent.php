<?php

namespace Magpie\Queues\Events;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Magpie\Events\BaseEvent;

/**
 * Indicate that the current worker process is started
 */
class WorkerStartedEvent extends BaseEvent
{
    /**
     * @var CarbonImmutable When worker started
     */
    public readonly CarbonImmutable $startedAt;


    /**
     * Constructor
     * @param CarbonInterface $startedAt
     */
    protected function __construct(CarbonInterface $startedAt)
    {
        $this->startedAt = $startedAt->toImmutable();
    }


    /**
     * Create an instance
     * @param CarbonInterface $startedAt
     * @return static
     */
    public static function create(CarbonInterface $startedAt) : static
    {
        return new static($startedAt);
    }
}