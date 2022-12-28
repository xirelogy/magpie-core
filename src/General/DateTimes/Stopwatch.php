<?php

namespace Magpie\General\DateTimes;

use Magpie\General\Traits\StaticCreatable;

/**
 * Stopwatch to track time difference
 */
class Stopwatch
{
    use StaticCreatable;

    /**
     * @var array<int> When started
     */
    protected array $started;


    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->started = hrtime();
    }


    /**
     * Stop the stopwatch and calculate the duration in-between
     * @return Duration|null
     */
    public function stop() : ?Duration
    {
        $ended = hrtime();

        [$startedSec, $startedNanoSec] = $this->started;
        [$endedSec, $endedNanoSec] = $ended;

        if ($startedSec > $endedSec) return null;
        if ($startedSec == $endedSec) {
            if ($startedNanoSec > $endedNanoSec) return null;
            return Duration::inNanoseconds($endedNanoSec - $startedNanoSec);
        }

        $diffSec = $endedSec - $startedSec;
        return Duration::inNanoseconds($diffSec * 1000000000 + $endedNanoSec - $startedNanoSec);
    }


    /**
     * If already timeout
     * @param Duration|null $timeout
     * @return bool
     */
    public function isTimeout(?Duration $timeout) : bool
    {
        // Not specified, no timeout
        if ($timeout === null) return false;

        // Will always time out for zero or less
        if ($timeout->getBaseValue() <= 0) return true;

        // Try to get elapsed time
        $elapsed = $this->stop();
        if ($elapsed === null) return false;

        // Unify the precision and do comparison
        Duration::unifyPrecision($elapsed, $timeout);
        return $elapsed->getBaseValue() > $timeout->getBaseValue();
    }
}