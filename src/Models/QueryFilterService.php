<?php

namespace Magpie\Models;

/**
 * Service provider for query filtering
 */
abstract class QueryFilterService
{
    /**
     * Duplicate the current query for other use
     * @return Query
     */
    public abstract function duplicateQuery() : Query;


    /**
     * Apply limit to the query statement
     * @param int|null $value
     * @return void
     */
    public abstract function setLimit(?int $value) : void;


    /**
     * Apply offset to the query statement
     * @param int|null $value
     * @return void
     */
    public abstract function setOffset(?int $value) : void;
}