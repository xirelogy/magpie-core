<?php

namespace Magpie\System\Concepts;

use Magpie\General\Concepts\Dispatchable;
use Magpie\General\DateTimes\Duration;

/**
 * May poll for messages (events) within main loop
 */
interface MainLoopPollable
{
    /**
     * Priority of the current poll, with lower number have higher priority
     * @return int
     */
    public function getPriority() : int;


    /**
     * If current poll supports idle mode
     * @return bool
     */
    public function isSupportIdle() : bool;


    /**
     * Poll for dispatchable items
     * @param Duration|null $idle When provided, time to keep idle
     * @return iterable<Dispatchable>
     */
    public function poll(?Duration $idle) : iterable;
}