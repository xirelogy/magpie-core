<?php

namespace Magpie\Facades\Redis;

/**
 * Query from sorted-set operation for redis with specific options
 */
abstract class RedisSortedSetGetCommand extends RedisQueryCommand
{
    /**
     * @var float|null Minimum score in range
     */
    protected ?float $minScore = null;
    /**
     * @var bool If minimum score is inclusive
     */
    protected bool $isMinInclusive = false;
    /**
     * @var float|null Maximum score in range
     */
    protected ?float $maxScore = null;
    /**
     * @var bool If maximum score is inclusive
     */
    protected bool $isMaxInclusive = false;
    /**
     * @var RedisSortOrder Sorting order
     */
    protected RedisSortOrder $order = RedisSortOrder::ASC;


    /**
     * Specify query score (minimal)
     * @param float|null $score
     * @param bool $isInclusive
     * @return $this
     */
    public function withMinScore(?float $score, bool $isInclusive = true) : static
    {
        $this->minScore = $score;
        $this->isMinInclusive = $isInclusive;
        return $this;
    }


    /**
     * Specify query score (maximal)
     * @param float|null $score
     * @param bool $isInclusive
     * @return $this
     */
    public function withMaxScore(?float $score, bool $isInclusive = true) : static
    {
        $this->maxScore = $score;
        $this->isMaxInclusive = $isInclusive;
        return $this;
    }


    /**
     * Specify sorting order
     * @param RedisSortOrder $order
     * @return $this
     */
    public function withSortOrder(RedisSortOrder $order) : static
    {
        $this->order = $order;
        return $this;
    }
}