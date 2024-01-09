<?php

namespace Magpie\Models\Providers\Sqlite;

use Magpie\Models\Providers\Pdo\PdoQueryGrammar;

/**
 * SQLite specific query grammar
 */
class SqliteQueryGrammar extends PdoQueryGrammar
{
    /**
     * @inheritDoc
     */
    public function applyLimit(string $sql, int $value) : string
    {
        return $sql . ' LIMIT ' . $value;
    }


    /**
     * @inheritDoc
     */
    public function applyOffset(string $sql, int $value) : string
    {
        return $sql . ' OFFSET ' . $value;
    }
}