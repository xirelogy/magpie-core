<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Constants;

/**
 * SQLite table option
 */
enum SqliteTableOption : string
{
    /**
     * Strict table types
     */
    case STRICT = 'strict';
    /**
     * No 'rowid' for this table
     */
    case WITHOUT_ROWID = 'without-rowid';
}