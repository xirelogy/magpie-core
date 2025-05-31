<?php

namespace Magpie\Models;

/**
 * Seeder to create query statement
 */
class QueryStatementCreator
{
    /**
     * @var string The SQL part of the statement
     */
    public string $sql;
    /**
     * @var array Values to bind with statement
     */
    public array $values;


    /**
     * Constructor
     * @param string $sql
     * @param array $values
     */
    public function __construct(string $sql, array $values = [])
    {
        $this->sql = $sql;
        $this->values = $values;
    }
}