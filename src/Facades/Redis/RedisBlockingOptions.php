<?php

namespace Magpie\Facades\Redis;

use Magpie\General\DateTimes\Duration;

/**
 * Options for redis blocking-operations
 */
class RedisBlockingOptions extends RedisOptions
{
    /**
     * @var Duration
     */
    public readonly Duration $timeout;


    /**
     * Constructor
     * @param Duration $timeout
     */
    protected function __construct(Duration $timeout)
    {
        $this->timeout = $timeout;
    }


    /**
     * Create options with specific timeout
     * @param Duration|int $timeout
     * @return static
     */
    public static function withTimeout(Duration|int $timeout) : static
    {
        $timeout = Duration::accept($timeout);
        return new static($timeout);
    }
}