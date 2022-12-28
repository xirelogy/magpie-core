<?php

namespace Magpie\Facades\Redis;

use Magpie\Facades\Redis\Concepts\RedisConditionalCommandable;
use Magpie\Facades\Redis\Traits\CommonRedisCommandConditional;

/**
 * Push to sorted-set operation for redis with specific options
 */
abstract class RedisSortedSetPushCommand extends RedisExecutableCommand implements RedisConditionalCommandable
{
    use CommonRedisCommandConditional;

    /**
     * @var array<float> Associated scores
     */
    protected array $scores = [];
    /**
     * @var array Associated values
     */
    protected array $values = [];


    /**
     * Add a score-value pair
     * @param float $score
     * @param mixed $value
     * @return $this
     */
    public function add(float $score, mixed $value) : static
    {
        $this->scores[] = $score;
        $this->values[] = $value;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public abstract function go() : int;
}