<?php

namespace Magpie\Models\Impls;

use Magpie\General\Sugars\Quote;
use Magpie\General\Traits\StaticClass;

/**
 * Helper for SQL formatting
 * @internal
 */
class SqlFormat
{
    use StaticClass;


    /**
     * Wrap payload in back-tick
     * @param string $payload
     * @return string
     */
    public static function backTick(string $payload) : string
    {
        if ($payload === '*') return $payload;  // '*' is a special case

        return Quote::backTick($payload);
    }
}