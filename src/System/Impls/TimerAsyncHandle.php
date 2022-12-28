<?php

namespace Magpie\System\Impls;

use Exception;
use Fiber;
use Magpie\Exceptions\OperationFailedException;
use Magpie\Facades\Random;
use Magpie\General\Concepts\Dispatchable;
use Magpie\General\DateTimes\Duration;
use Magpie\General\DateTimes\Specific\DurationInNanoseconds;
use Magpie\General\DateTimes\Stopwatch;
use Magpie\General\Randoms\RandomCharset;
use Magpie\General\Sugars\Excepts;
use Magpie\System\Kernel\EasyFiber;

/**
 * Representation of an asynchronous timer handle
 * @internal
 */
class TimerAsyncHandle implements Dispatchable
{
    /**
     * @var string Unique identity
     */
    public readonly string $id;
    /**
     * @var Duration Timer duration
     */
    public readonly Duration $duration;
    /**
     * @var int Timer duration
     */
    protected readonly int $durationNs;
    /**
     * @var Stopwatch|null Associated stopwatch
     */
    protected ?Stopwatch $stopwatch = null;
    /**
     * @var Fiber|null Waiting fiber
     */
    protected ?Fiber $waitFiber = null;


    /**
     * Constructor
     * @param Duration|int $duration
     */
    public function __construct(Duration|int $duration)
    {
        $this->id = Random::string(16, RandomCharset::LOWER_ALPHANUM);
        $this->duration = Duration::accept($duration);
        $this->durationNs = $this->duration->getValueAtPrecisionScale(DurationInNanoseconds::SCALE);
    }


    /**
     * Wait for timer to expire
     * @param Fiber $fiber
     * @return void
     * @throws OperationFailedException
     */
    public function asyncWait(Fiber $fiber) : void
    {
        $this->stopwatch = Stopwatch::create();
        $this->waitFiber = $fiber;

        try {
            EasyFiber::suspend();
        } catch (Exception $ex) {
            throw new OperationFailedException(previous: $ex);
        } finally {
            $this->waitFiber = null;
        }
    }


    /**
     * Check if current handle is dispatchable
     * @return Dispatchable|null
     */
    public function checkDispatchable() : ?Dispatchable
    {
        if ($this->stopwatch === null) return null;

        $elapsed = $this->stopwatch->stop();
        if ($elapsed === null) return null;

        $elapsedNs = $elapsed->getValueAtPrecisionScale(DurationInNanoseconds::SCALE);
        if ($elapsedNs < $this->durationNs) return null;

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function dispatch() : void
    {
        if ($this->waitFiber !== null) {
            Excepts::noThrow(fn () => EasyFiber::resume($this->waitFiber, null));
        }
    }
}