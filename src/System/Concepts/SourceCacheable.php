<?php

namespace Magpie\System\Concepts;

use Exception;

/**
 * Implement this interface to support caching to PHP source code. This is normally applicable
 * to maps created via attributes discovery
 */
interface SourceCacheable
{
    /**
     * Save into source cache
     * @return void
     * @throws Exception
     */
    public static function saveSourceCache() : void;


    /**
     * Delete any existing source cache
     * @return void
     * @throws Exception
     */
    public static function deleteSourceCache() : void;
}