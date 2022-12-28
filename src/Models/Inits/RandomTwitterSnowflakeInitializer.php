<?php

namespace Magpie\Models\Inits;

use Carbon\CarbonInterface;
use Magpie\Facades\Random;

/**
 * Flavor of Twitter's snowflake ID generator with random node
 */
class RandomTwitterSnowflakeInitializer extends TwitterSnowflakeInitializer
{
    /**
     * @var int|null Last used timestamp
     */
    protected static ?int $lastMilliTimestamp = null;


    /**
     * @inheritDoc
     */
    protected static function getNode() : int
    {
        return Random::integer(0, pow(2, static::NODE_BITS) - 1);
    }


    /**
     * @inheritDoc
     */
    protected static function getSequence(int &$milliTimestamp, ?CarbonInterface $specificTime) : int
    {
        if ($specificTime === null && static::$lastMilliTimestamp !== null) {
            // Prevent millisecond clash when specific time is not used
            while ($milliTimestamp <= static::$lastMilliTimestamp) {
                usleep(1);
                $milliTimestamp = static::getCurrentMilliTimestamp($specificTime);
            }
        }

        static::$lastMilliTimestamp = $milliTimestamp;
        return Random::integer(0, pow(2, static::SEQUENCE_BITS) - 1);
    }
}