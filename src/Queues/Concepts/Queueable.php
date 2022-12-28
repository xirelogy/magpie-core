<?php

namespace Magpie\Queues\Concepts;

use Carbon\CarbonInterface;
use Magpie\General\Concepts\Identifiable;
use Magpie\General\Concepts\Runnable;
use Magpie\General\DateTimes\Duration;
use Magpie\Queues\Simples\QueueArguments;

/**
 * Anything queueable (can be enqueued to queues)
 */
interface Queueable extends Runnable, Identifiable
{
    /**
     * Specify the preferred name for the queue item
     * @param string $name
     * @return $this
     */
    public function withName(string $name) : static;


    /**
     * Specify the maximum number of attempts to run this queue item
     * @param int $attempts
     * @return $this
     */
    public function withMaxAttempts(int $attempts) : static;


    /**
     * Specify the delay between attempts to run this queue item
     * @param Duration $delay
     * @return $this
     */
    public function withRetryDelay(Duration $delay) : static;


    /**
     * Specify the maximum time allowed to run this queue item
     * @param Duration|null $timeout The maximum timeout, or no limitation if not specified (or less than equals zero)
     * @return $this
     */
    public function withRunningTimeout(?Duration $timeout) : static;


    /**
     * Specify the name of the queue where this item should be queued on
     * @param string $name
     * @return $this
     */
    public function withQueue(string $name) : static;


    /**
     * Delay before current item is executable
     * @param Duration|CarbonInterface $delay
     * @return $this
     */
    public function withDelay(Duration|CarbonInterface $delay) : static;


    /**
     * Get queue arguments
     * @return QueueArguments
     */
    public function getQueueArguments() : QueueArguments;


    /**
     * Get queue target
     * @return QueueRunnable
     */
    public function getQueueTarget() : QueueRunnable;
}