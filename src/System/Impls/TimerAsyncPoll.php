<?php

namespace Magpie\System\Impls;

use Magpie\General\Concepts\Dispatchable;
use Magpie\General\DateTimes\Duration;
use Magpie\General\DateTimes\Specific\DurationInMicroseconds;
use Magpie\General\Traits\SingletonInstance;
use Magpie\System\Concepts\MainLoopPollable;
use Magpie\System\Kernel\MainLoop;

/**
 * Poll for timers
 * @internal
 */
class TimerAsyncPoll implements MainLoopPollable
{
    use SingletonInstance;

    /**
     * @var bool If registered
     */
    protected bool $isRegistered = false;
    /**
     * @var array<string, TimerAsyncHandle> Registered handles
     */
    protected array $handles = [];


    /**
     * Register a handle
     * @param TimerAsyncHandle $handle
     * @return void
     */
    public function registerHandle(TimerAsyncHandle $handle) : void
    {
        // Register to the event loop
        if (!$this->isRegistered) {
            MainLoop::registerPoll($this);
            $this->isRegistered = true;
        }

        $this->handles[$handle->id] = $handle;
    }


    /**
     * Deregister a handle
     * @param TimerAsyncHandle $handle
     * @return void
     */
    public function deregisterHandle(TimerAsyncHandle $handle) : void
    {
        unset($this->handles[$handle->id]);

        if (count($this->handles) <= 0 && $this->isRegistered) {
            MainLoop::deregisterPoll($this);
            $this->isRegistered = false;
        }
    }


    /**
     * @inheritDoc
     */
    public function getPriority() : int
    {
        return MainLoop::PRIORITY_TIME;
    }


    /**
     * @inheritDoc
     */
    public function isSupportIdle() : bool
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    public function poll(?Duration $idle) : iterable
    {
        $dispatches = $this->collectDispatches();
        yield from $dispatches;

        if ($idle !== null && count($dispatches) <= 0) {
            $idleUSec = $idle->getValueAtPrecisionScale(DurationInMicroseconds::SCALE);
            if ($idleUSec < 100) $idleUSec = 100;   // Force a healthy value

            usleep($idleUSec);

            yield from $this->collectDispatches();
        }
    }


    /**
     * Collect dispatches from handles
     * @return array<Dispatchable>
     */
    protected function collectDispatches() : array
    {
        $ret = [];
        foreach ($this->handles as $handle) {
            $dispatch = $handle->checkDispatchable();
            if ($dispatch !== null) $ret[] = $dispatch;
        }

        return $ret;
    }
}