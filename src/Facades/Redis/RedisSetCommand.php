<?php

namespace Magpie\Facades\Redis;

use Magpie\Facades\Redis\Concepts\RedisConditionalCommandable;
use Magpie\Facades\Redis\Traits\CommonRedisCommandConditional;
use Magpie\General\DateTimes\Duration;

/**
 * Set operation for redis with specific options
 */
abstract class RedisSetCommand extends RedisExecutableCommand implements RedisConditionalCommandable
{
    use CommonRedisCommandConditional;


    /**
     * @var Duration|null Specific time-to-live
     */
    protected ?Duration $ttl = null;


    /**
     * With specific time-to-live of the key-value with reference to now
     * @param int|Duration|null $ttl
     * @return $this
     */
    public function withTtl(int|Duration|null $ttl) : static
    {
        $this->ttl = Duration::accept($ttl);
        return $this;
    }


    /**
     * @inheritDoc
     */
    public abstract function go() : bool;
}