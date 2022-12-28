<?php

namespace Magpie\Models\Providers\Mysql;

use Magpie\Models\Providers\Pdo\PdoQueryGrammar;

/**
 * MySQL specific query grammar
 */
class MysqlQueryGrammar extends PdoQueryGrammar
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