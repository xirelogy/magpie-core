<?php

namespace Magpie\System\HardCore;

use Magpie\Facades\Log;
use Magpie\Facades\Random;
use Magpie\General\Randoms\RandomCharset;
use Magpie\General\Traits\StaticClass;

/**
 * Management of a global state
 */
class GlobalState
{
    use StaticClass;

    /**
     * @var bool If the state is marked
     */
    private static bool $isMarked = false;
    /**
     * @var string|null Current episode
     */
    private static ?string $episode = null;


    /**
     * Mark the global state
     * @return void
     */
    public static function mark() : void
    {
        static::$isMarked = true;
    }


    /**
     * Check if the global state is marked
     * @return bool
     */
    public static function isMarked() : bool
    {
        return static::$isMarked;
    }


    /**
     * Get the current episode (generate one if not yet exist)
     * @return string
     */
    public static function getEpisode() : string
    {
        if (static::$episode === null) {
            static::$episode = Random::string(16, RandomCharset::LOWER_ALPHANUM);
        }

        return static::$episode;
    }


    /**
     * Set (override) the current episode
     * @param string $episode
     * @return void
     */
    public static function setEpisode(string $episode) : void
    {
        if (static::$episode !== null && static::$episode != $episode) {
            Log::warning('Global episode was overridden');
        }

        static::$episode = $episode;
    }
}