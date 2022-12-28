<?php

namespace Magpie\Caches\Concepts;

use Magpie\General\Concepts\Deletable;
use Magpie\General\Concepts\Savable;
use Magpie\General\DateTimes\Duration;

/**
 * Anything that can be stored in the cache
 */
interface Cacheable extends Savable, Deletable
{
    /**
     * Key to identify in cache
     * @return string
     */
    public function getCacheKey() : string;


    /**
     * Timeout before removed from cache
     * @return Duration|null
     */
    public function getCacheTimeout() : ?Duration;
}