<?php

namespace Magpie\Queues\Commands;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Exception;
use Magpie\Codecs\Parsers\BooleanParser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Commands\Attributes\CommandDescription;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Events\ClosureEventTemporaryReceiver;
use Magpie\Events\Concepts\Eventable;
use Magpie\Events\EventDelivery;
use Magpie\Facades\Console;
use Magpie\General\DateTimes\Duration;
use Magpie\Queues\Events\QueuedItemCompletedEvent;
use Magpie\Queues\Events\QueuedItemEvent;
use Magpie\Queues\Events\QueuedItemExceptionEvent;
use Magpie\Queues\Events\QueuedItemFailedEvent;
use Magpie\Queues\Events\QueuedItemRunningEvent;
use Magpie\Queues\Events\WorkerKillEvent;
use Magpie\Queues\Providers\Queue;
use Magpie\Queues\Providers\QueueCreator;

#[CommandSignature('queue:run-worker {--once} {--queue=} {--timeout=}')]
#[CommandDescription('Run queue\'s worker')]
class RunWorkerCommand extends Command
{
    /**
     * @var bool If loop is running
     */
    protected bool $isRunning = false;
    /**
     * @var int Total jobs processed
     */
    protected int $totalProcessed = 0;


    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        $isOnce = $request->options->requires('once', BooleanParser::create());
        $queueName = $request->options->optional('queue', StringParser::create());
        $timeout = $request->options->optional('timeout', Duration::createSecondParser(), $isOnce ? 30 : 5);

        $queue = QueueCreator::instance()->getQueue($queueName);

        // Handle the queue job events
        EventDelivery::subscribe([
            QueuedItemRunningEvent::class,
            QueuedItemCompletedEvent::class,
            QueuedItemExceptionEvent::class,
            QueuedItemFailedEvent::class,
        ], ClosureEventTemporaryReceiver::create(function (Eventable $event) : void {
            if (!$event instanceof QueuedItemEvent) return;

            $displayName = $event->getEventState()->getDisplayName();
            $showExceptionFn = function() use($event) {
                $ex = $event->getEventState()->getLastException();
                if ($ex === null) return;
                Console::error($ex->getMessage());
                Console::warning($ex->getTraceAsString());
            };

            switch ($event::class) {
                case QueuedItemRunningEvent::class:
                    Console::info(_format_safe(_l('{{0}} running...'), $displayName) ?? _l('Job running...'));
                    break;
                case QueuedItemCompletedEvent::class:
                    Console::info(_format_safe(_l('{{0}} completed'), $displayName) ?? _l('Job completed'));
                    break;
                case QueuedItemExceptionEvent::class:
                    Console::error(_format_safe(_l('{{0}} crashed with exception'), $displayName) ?? _l('Job crashed with exception'));
                    $showExceptionFn();
                    break;
                case QueuedItemFailedEvent::class:
                    Console::error(_format_safe(_l('{{0}} failed'), $displayName) ?? _l('Job failed'));
                    $showExceptionFn();
                    break;
                default:
                    break;
            }
        }));

        // Handle worker events
        EventDelivery::subscribe(WorkerKillEvent::class, ClosureEventTemporaryReceiver::create($this->kill(...)));

        // Enable asynchronous signals when available
        if (extension_loaded('pcntl')) {
            pcntl_async_signals(true);

            pcntl_signal(SIGTERM, function() : void {
                $this->isRunning = false;
            });
        }

        if ($isOnce) {
            // Run current worker only once
            $this->waitOnQueue($queue, $timeout);
        } else {
            // Run and host worker in a recursive loop
            $this->loopOnQueue($queue, $timeout);
        }
    }


    /**
     * Loop and decode on queue
     * @param Queue $queue
     * @param Duration $timeout
     * @return void
     * @throws Exception
     */
    protected function loopOnQueue(Queue $queue, Duration $timeout) : void
    {
        $started = Carbon::now();
        $this->isRunning = true;

        for (;;) {
            if (!$this->isLoop($queue, $started)) break;
            $this->waitOnQueue($queue, $timeout);
        }
    }


    /**
     * Wait and decode on queue
     * @param Queue $queue
     * @param Duration $timeout
     * @return void
     * @throws Exception
     */
    protected function waitOnQueue(Queue $queue, Duration $timeout) : void
    {
        $decoded = $queue->dequeue($timeout);
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
     * @param Queue $queue
     * @param CarbonInterface $started
     * @return bool
     * @throws Exception
     */
    protected function isLoop(Queue $queue, CarbonInterface $started) : bool
    {
        if (!$this->isRunning) return false;

        if ($queue->shallWorkerRestart($started)) return false;

        return true;
    }


    /**
     * Kill the current worker process
     * @return never
     */
    protected final function kill() : never
    {
        // May use posix signal to suicide
        if (extension_loaded('posix') && extension_loaded('pcntl')) {
            posix_kill(getmypid(), SIGKILL);
        }

        exit(Command::EXIT_FAILURE);
    }
}