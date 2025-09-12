<?php

namespace Magpie\Models\Providers;

use Magpie\Models\Concepts\QueryIdentifierQuotable;

/**
 * Implementation specific query grammar
 */
abstract class QueryGrammar
{
    /**
     * Instance to quote identifier
     * @return QueryIdentifierQuotable
     */
    public function getIdentifierQuote() : QueryIdentifierQuotable
    {
        return DefaultQueryIdentifierQuote::instance();
    }


    /**
     * Apply 'LIMIT' to given SQL query
     * @param string $sql
     * @param int $value
     * @return string
     */
    public abstract function applyLimit(string $sql, int $value) : string;


    /**
     * Apply 'OFFSET' to given SQL query
     * @param string $sql
     * @param int $value
     * @return string
     */
    public abstract function applyOffset(string $sql, int $value) : string;
}