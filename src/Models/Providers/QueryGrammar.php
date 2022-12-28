<?php

namespace Magpie\Models\Providers;

/**
 * Implementation specific query grammar
 */
abstract class QueryGrammar
{
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