<?php

namespace Magpie\System\Concepts;

use Exception;

/**
 * May translate into a format suitable to be stored as source cache, handling both exporting and importing
 */
interface SourceCacheTranslatable
{
    /**
     * Export for source cache
     * @return array
     * @throws Exception
     */
    public function sourceCacheExport() : array;


    /**
     * Import from source cache
     * @param array $data
     * @return static
     * @throws Exception
     */
    public static function sourceCacheImport(array $data) : static;
}