<?php

namespace Magpie\Queues;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Exception;
use Magpie\Exceptions\OperationFailedException;
use Magpie\General\DateTimes\Duration;
use Magpie\General\Traits\ReleaseOnDestruct;
use Magpie\Queues\Concepts\QueueExecutable;
use Magpie\Queues\Concepts\QueueRunnable;
use Magpie\Queues\Events\QueuedItemCompletedEvent;
use Magpie\Queues\Events\QueuedItemExceptionEvent;
use Magpie\Queues\Events\QueuedItemFailedEvent;
use Magpie\Queues\Events\QueuedItemRunningEvent;
use Magpie\Queues\Events\WorkerKillEvent;
use Magpie\Queues\Exceptions\MaxAttemptsExceededException;
use Magpie\Queues\Exceptions\QueueExecutionTimeoutException;
use Magpie\Queues\Impls\QueueJobTimeout;
use Magpie\Queues\Simples\FailedExecutableEncoded;
use Magpie\System\Kernel\ExceptionHandler;
use Throwable;

/**
 * A pending executable unit from the queue
 */
abstract class PendingExecutable implements QueueExecutable
{
    use ReleaseOnDestruct;

    /**
     * @var bool If current executable is released
     */
    private bool $isReleased = false;
    /**
     * @var bool If ran successfully
     */
    private bool $isSuccess = false;
    /**
     * @var bool If crashed (no longer allow retry)
     */
    private bool $isCrashed = false;
    /**
     * @var Throwable|null Last exception during execution
     */
    private ?Throwable $lastException = null;


    /**
     * @inheritDoc
     */
    public final function release() : void
    {
        if ($this->isReleased) return;

        $this->isReleased = true;

        if ($this->isSuccess) {
            // Current job is deleted
            $this->releaseFromQueue();
        } else if ($this->isCrashed) {
            // Current job crashed, no longer posted in release
            $this->releaseFromQueue();
        } else {
            // Post current job into the queue again at a later backoff
            $matureAt = $this->calculateBackoffMaturity();
            $this->repostLaterOnQueue($matureAt);
        }
    }


    /**
     * Display name
     * @return string
     */
    public final function getDisplayName() : string
    {
        return $this->getId() . '@[' . $this->getName() . ']';
    }


    /**
     * Current attempt number
     * @return int
     * @throws Exception
     */
    protected abstract function getCurrentAttempt() : int;


    /**
     * Maximum number of attempts
     * @return int
     * @throws Exception
     */
    protected abstract function getMaxAttempts() : int;


    /**
     * Release current executable from queue
     * @return void
     */
    protected abstract function releaseFromQueue() : void;


    /**
     * Repost current executable to be attempted later at given maturity
     * @param CarbonInterface $matureAt
     * @return void
     */
    protected abstract function repostLaterOnQueue(CarbonInterface $matureAt) : void;


    /**
     * Calculate backoff maturity for future attempts
     * @return CarbonInterface
     */
    protected function calculateBackoffMaturity() : CarbonInterface
    {
        try {
            $delay = $this->getRetryDelay();
            if ($delay instanceof CarbonInterface) return $delay;

            return Carbon::now()->addSeconds($delay->getSeconds());
        } catch (Exception) {
            return Carbon::now();
        }
    }


    /**
     * Get delay for retry
     * @return Duration|CarbonInterface
     * @throws Exception
     */
    protected abstract function getRetryDelay() : Duration|CarbonInterface;


    /**
     * Get the maximum running timeout
     * @return Duration
     * @throws Exception
     */
    protected abstract function getRunningTimeout() : Duration;


    /**
     * Get running target
     * @return QueueRunnable
     */
    protected abstract function getTarget() : QueueRunnable;


    /**
     * If allowed to be run
     * @return bool
     */
    protected function isRunnable() : bool
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    public function run() : void
    {
        QueuedItemRunningEvent::create($this)->run();

        if (!$this->preflightCheck()) return;

        $runningTimeout = $this->getRunningTimeout();
        $runningTimeoutLock = new QueueJobTimeout($runningTimeout, function() use($runningTimeout) : never {
            $ex = new QueueExecutionTimeoutException($runningTimeout);
            $this->reportException($ex);

            exit();
        });

        _used($runningTimeoutLock);

        try {
            $this->getTarget()->run();
            $this->isSuccess = true;

            QueuedItemCompletedEvent::create($this)->run();
        } catch (Throwable $ex) {
            $this->reportException($ex);
        }
    }


    /**
     * Last caused exception
     * @return Throwable|null
     */
    public final function getLastException() : ?Throwable
    {
        return $this->lastException;
    }


    /**
     * Report the exception
     * @param Throwable $ex
     * @return void
     */
    protected final function reportException(Throwable $ex) : void
    {
        $this->lastException = $ex;
        QueuedItemExceptionEvent::create($this)->safeRun();
    }


    /**
     * Report the timeout exception
     * @param Duration $timeout
     * @return never
     */
    protected final function reportTimeout(Duration $timeout) : never
    {
        // Create exception and report
        $ex = new QueueExecutionTimeoutException($timeout);
        $this->reportException($ex);

        WorkerKillEvent::create()->safeRun();

        // Should not reach here, but if eventually so, exit
        exit();
    }


    /**
     * Fail using given exception
     * @param Throwable|null $ex
     * @return void
     */
    protected final function fail(?Throwable $ex = null) : void
    {
        $ex = $ex ?? new OperationFailedException();

        $this->isCrashed = true;
        $this->lastException = $ex;
        QueuedItemFailedEvent::create($this)->safeRun();

        try {
            $encoded = $this->encodeFailed(Carbon::now(), $ex);
            QueueFailHandler::getCurrent()->handleFailed($encoded);
        } catch (Exception $ex) {
            ExceptionHandler::systemCritical($ex);
        }
    }


    /**
     * Encode current item with failure
     * @param CarbonInterface $happenedAt
     * @param Throwable $ex
     * @return FailedExecutableEncoded
     */
    protected abstract function encodeFailed(CarbonInterface $happenedAt, Throwable $ex) : FailedExecutableEncoded;


    /**
     * Check before execution
     * @return bool
     */
    private function preflightCheck() : bool
    {
        try {
            $this->onPreflightCheck();
            return true;
        } catch (Exception $ex) {
            $this->fail($ex);
            return false;
        }
    }


    /**
     * Check procedures before execution
     * @return void
     * @throws Exception
     */
    protected function onPreflightCheck() : void
    {
        // Check if allowed to be run
        if (!$this->isRunnable()) return;

        // Check maximum number of attempts
        $currentAttempt = $this->getCurrentAttempt();
        $maxAttempts = $this->getMaxAttempts();

        if ($currentAttempt > $maxAttempts) {
            throw new MaxAttemptsExceededException($currentAttempt, $maxAttempts);
        }
    }
}