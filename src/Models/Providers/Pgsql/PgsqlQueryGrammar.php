<?php

namespace Magpie\Models\Providers\Pgsql;

use Magpie\Models\Providers\Pdo\PdoQueryGrammar;

/**
 * PostgreSQL specific query grammar
 */
class PgsqlQueryGrammar extends PdoQueryGrammar
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