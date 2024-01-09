<?php

namespace Magpie\Models\Providers\Sqlite\Impls;

use Magpie\General\Sugars\Quote;
use Magpie\General\Traits\StaticClass;

/**
 * SQLite related grammar
 * @internal
 */
class SqliteGrammar
{
    use StaticClass;


    /**
     * Escape SQL name
     * @param string $name
     * @return string
     */
    public static function escapeName(string $name) : string
    {
        return Quote::square($name);
    }
}