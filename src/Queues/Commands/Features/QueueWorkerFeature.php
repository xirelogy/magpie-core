<?php

namespace Magpie\Queues\Commands\Features;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Exception;
use Magpie\Commands\Command;
use Magpie\Events\ClosureEventTemporaryReceiver;
use Magpie\Events\Concepts\EventTemporaryReceivable;
use Magpie\Events\EventDelivery;
use Magpie\Exceptions\InvalidStateException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\DateTimes\Duration;
use Magpie\Queues\Events\QueuedItemCompletedEvent;
use Magpie\Queues\Events\QueuedItemExceptionEvent;
use Magpie\Queues\Events\QueuedItemFailedEvent;
use Magpie\Queues\Events\QueuedItemRunningEvent;
use Magpie\Queues\Events\WorkerKillEvent;
use Magpie\Queues\Events\WorkerStartedEvent;
use Magpie\Queues\Providers\Queue;
use Magpie\Queues\Providers\QueueCreator;

/**
 * Queue worker running features
 */
class QueueWorkerFeature
{
    /**
     * @var Queue Target queue instance
     */
    protected readonly Queue $queue;
    /**
     * @var Duration Timeout during run
     */
    protected readonly Duration $timeout;
    /**
     * @var bool If started
     */
    protected bool $isStarted = false;
    /**
     * @var bool If loop is running
     */
    protected bool $isRunning = false;
    /**
     * @var int Total jobs processed
     */
    protected int $totalProcessed = 0;


    /**
     * Constructor
     * @param string|null $queueName
     * @param Duration $timeout
     * @throws SafetyCommonException
     */
    protected function __construct(?string $queueName, Duration $timeout)
    {
        $this->queue = QueueCreator::instance()->getQueue($queueName);
        $this->timeout = $timeout;
    }


    /**
     * Run the queue
     * @param bool $isOnce
     * @param EventTemporaryReceivable|null $queueReceiver
     * @return void
     * @throws Exception
     */
    protected function onRun(bool $isOnce, ?EventTemporaryReceivable $queueReceiver) : void
    {
        // Do not allow rerun
        if ($this->isStarted) throw new InvalidStateException();
        $this->isStarted = true;

        // Subscribe to queue events
        if ($queueReceiver !== null) {
            EventDelivery::subscribe([
                QueuedItemRunningEvent::class,
                QueuedItemCompletedEvent::class,
                QueuedItemExceptionEvent::class,
                QueuedItemFailedEvent::class,
            ], $queueReceiver);
        }

        // Handle internal worker events
        EventDelivery::subscribe(WorkerKillEvent::class, ClosureEventTemporaryReceiver::create(static::kill(...)));

        // Enable asynchronous signals when available
        if (extension_loaded('pcntl')) {
            pcntl_async_signals(true);

            pcntl_signal(SIGTERM, function() : void {
                $this->isRunning = false;
            });
        }

        if ($isOnce) {
            // Run current worker only once
            $this->waitOnQueue();
        } else {
            // Run and host worker in a recursive loop
            $this->loopOnQueue();
        }
    }


    /**
     * Loop and decode on queue
     * @return void
     * @throws Exception
     */
    protected function loopOnQueue() : void
    {
        WorkerStartedEvent::create()->run();

        $started = Carbon::now();
        $this->isRunning = true;

        for (;;) {
            if (!$this->isLoop($started)) break;
            $this->waitOnQueue();
        }
    }


    /**
     * Wait and decode on queue
     * @return void
     * @throws Exception
     */
    protected function waitOnQueue() : void
    {
        $decoded = $this->queue->dequeue($this->timeout);
        if ($decoded === null) return;

        ++$this->totalProcessed;

        // Run and release
        try {
            $decoded->run();
        } finally {
            $decoded->release();
        }
    }


    /**
     * If current worker needs to be in the loop
     * @param CarbonInterface $started
     * @return bool
     * @throws Exception
     */
    protected function isLoop(CarbonInterface $started) : bool
    {
        if (!$this->isRunning) return false;

        if ($this->queue->shallWorkerRestart($started)) return false;

        return true;
    }


    /**
     * Kill the current worker process
     * @return never
     */
    protected static final function kill() : never
    {
        // May use posix signal to suicide
        if (extension_loaded('posix') && extension_loaded('pcntl')) {
            posix_kill(getmypid(), SIGKILL);
        }

        exit(Command::EXIT_FAILURE);
    }


    /**
     * Host and run the queue worker
     * @param string|null $queueName
     * @param bool $isOnce
     * @param Duration $timeout
     * @param EventTemporaryReceivable|null $queueReceiver
     * @return void
     * @throws Exception
     */
    public static function run(?string $queueName, bool $isOnce, Duration $timeout, ?EventTemporaryReceivable $queueReceiver = null) : void
    {
        $instance = new static($queueName, $timeout);
        $instance->onRun($isOnce, $queueReceiver);
    }
}