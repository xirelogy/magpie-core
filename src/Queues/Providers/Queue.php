<?php

namespace Magpie\Queues\Providers;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\DateTimes\Duration;
use Magpie\Queues\Concepts\Dequeueable;
use Magpie\Queues\Concepts\Enqueueable;
use Magpie\Queues\Simples\FailedExecutableEncoded;

/**
 * A queue for 'job' to be executed at a different context, different time
 */
abstract class Queue implements Enqueueable, Dequeueable
{
    /**
     * Default queue name
     */
    protected const NAME_DEFAULT = 'default';

    /**
     * @var string Queue name
     */
    protected string $name;
    /**
     * @var Duration Timeout for retrying job
     */
    protected Duration $retryTimeout;


    /**
     * Constructor
     * @param string|null $name
     * @param Duration|null $retryTimeout
     */
    public function __construct(?string $name, ?Duration $retryTimeout)
    {
        $this->name = $name ?? static::NAME_DEFAULT;
        $this->retryTimeout = $retryTimeout ?? Duration::inSeconds(60);
    }


    /**
     * Current queue name
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }


    /**
     * Check against provided timestamp where worker is started, and decide if the worker shall be restarted
     * @param CarbonInterface $workerStarted
     * @return bool
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public abstract function shallWorkerRestart(CarbonInterface $workerStarted) : bool;


    /**
     * Send a signal to workers to restart
     * @param Duration|null $timeout
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public abstract function signalWorkerRestart(?Duration $timeout = null) : void;


    /**
     * Re-enqueue a failed job to the queue
     * @param FailedExecutableEncoded $failed
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public abstract function enqueueFailed(FailedExecutableEncoded $failed) : void;


    /**
     * Accept queue delay into maturity time
     * @param CarbonInterface|Duration|int|null $delay
     * @return CarbonInterface|null
     * @throws SafetyCommonException
     */
    protected static function acceptQueueDelay(CarbonInterface|Duration|int|null $delay) : ?CarbonInterface
    {
        if ($delay === null) return null;
        if ($delay instanceof CarbonInterface) return $delay;

        $delay = Duration::accept($delay);

        return Carbon::now()->add($delay->toCarbonInterval());
    }
}