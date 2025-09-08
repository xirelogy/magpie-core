<?php

namespace Magpie\Models\Inits;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Magpie\Exceptions\OutOfRangeSubjectException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Facades\Random;
use Magpie\Models\Concepts\AttributeInitializable;

/**
 * General implementation of Twitter's snowflake ID generator
 * @extends AttributeInitializable<int>
 */
abstract class TwitterSnowflakeInitializer implements AttributeInitializable
{
    /**
     * Twitter's default epoch: 2010-11-04 01:42:54.657 UTC
     */
    public const TWITTER_EPOCH = 1288834974657;
    /**
     * Discord's default epoch: 2015-01-01 00:00:00.000 UTC
     */
    public const DISCORD_EPOCH = 1420070400000;

    /**
     * Number of bits reserved (reserved bits must be 0 in value)
     */
    protected const RESERVED_BITS = 1;
    /**
     * Number of bits for timestamp
     */
    protected const TIMESTAMP_BITS = 41;
    /**
     * Number of bits for node
     */
    protected const NODE_BITS = 10;
    /**
     * Number of bits for sequences
     */
    protected const SEQUENCE_BITS = 12;

    /**
     * @var int|null Last specific generated timestamp's in millisecond
     */
    private static ?int $lastSpecificMilliTimestamp = null;


    /**
     * @inheritDoc
     */
    public final static function generate() : int
    {
        return static::onGenerate(null);
    }


    /**
     * Generate the next initialization value for specific time (in seconds)
     * @param CarbonInterface $specificTime
     * @return int
     * @throws SafetyCommonException
     */
    public final static function generateSpecific(CarbonInterface $specificTime) : int
    {
        return static::onGenerate($specificTime);
    }


    /**
     * Try to decode for associated time value from specific generated value
     * @param int|null $value
     * @return CarbonImmutable|null
     */
    public final static function decodeTime(?int $value) : ?CarbonImmutable
    {
        if ($value === null) return null;

        $diffMilliTimestamp = $value >> (static::NODE_BITS + static::SEQUENCE_BITS);
        $timestamp = $diffMilliTimestamp + static::getEpochMilliTimestamp();
        $timestamp = $timestamp & ((1 << static::TIMESTAMP_BITS) - 1);

        return CarbonImmutable::createFromTimestampMs($timestamp);
    }


    /**
     * Actual generation routine
     * @param CarbonInterface|null $specificTime
     * @return int
     * @throws SafetyCommonException
     */
    protected static function onGenerate(?CarbonInterface $specificTime) : int
    {
        $timestamp = static::getCurrentMilliTimestamp($specificTime);
        $node = static::getNode();
        $sequence = static::getSequence($timestamp, $specificTime);

        $diffMilliTimestamp = $timestamp - static::getEpochMilliTimestamp();

        if ($sequence < 0 || $sequence >= pow(2, static::SEQUENCE_BITS)) throw new OutOfRangeSubjectException(_l('sequence'));
        if ($node < 0 || $node >= pow(2, static::NODE_BITS)) throw new OutOfRangeSubjectException(_l('node'));
        if ($diffMilliTimestamp < 0 || $diffMilliTimestamp >= pow(2, static::TIMESTAMP_BITS)) throw new OutOfRangeSubjectException(_l('timestamp'));

        return ($diffMilliTimestamp << (static::NODE_BITS + static::SEQUENCE_BITS))
            | ($node << (static::SEQUENCE_BITS))
            | ($sequence);
    }


    /**
     * Get current timestamp (in milliseconds)
     * @param CarbonInterface|null $specificTime
     * @return int
     */
    protected static final function getCurrentMilliTimestamp(?CarbonInterface $specificTime) : int
    {
        $milliTimestamp = static::onGetCurrentMilliTimestamp($specificTime);
        if ($specificTime !== null) static::$lastSpecificMilliTimestamp = $milliTimestamp;
        return $milliTimestamp;
    }


    /**
     * Get current timestamp (in milliseconds), actually
     * @param CarbonInterface|null $specificTime
     * @return int
     */
    protected static function onGetCurrentMilliTimestamp(?CarbonInterface $specificTime) : int
    {
        if ($specificTime !== null) {
            $refTimestamp = $specificTime->getTimestamp() * 1000;
            $lastRefTimestamp = static::$lastSpecificMilliTimestamp ? intval(floor(static::$lastSpecificMilliTimestamp / 1000)) * 1000 : null;

            if ($lastRefTimestamp !== null && $lastRefTimestamp == $refTimestamp) {
                // Happened at the same time!
                return static::$lastSpecificMilliTimestamp + Random::integer(1, 3);
            } else {
                // Start low
                return ($specificTime->getTimestamp() * 1000) + Random::integer(0, 9);
            }
        }

        return floor(microtime(true) * 1000) | 0;
    }


    /**
     * Generate or obtain the node number
     * @return int
     * @throws SafetyCommonException
     */
    protected abstract static function getNode() : int;


    /**
     * Generate or obtain the sequence number
     * @param int $milliTimestamp
     * @param CarbonInterface|null $specificTime
     * @return int
     * @throws SafetyCommonException
     */
    protected abstract static function getSequence(int &$milliTimestamp, ?CarbonInterface $specificTime) : int;


    /**
     * Get epoch's timestamp (in milliseconds)
     * @return int
     */
    protected static function getEpochMilliTimestamp() : int
    {
        return static::TWITTER_EPOCH;
    }
}