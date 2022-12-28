<?php

namespace Magpie\Models\Impls;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Connection;
use Magpie\Models\Exceptions\ModelOperationFailedException;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelWriteException;
use Magpie\Models\Statement;

/**
 * A query statement
 * @internal
 */
class QueryStatement
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


    /**
     * Create corresponding statement associated to given connection
     * @param Connection $connection
     * @return Statement
     * @throws SafetyCommonException
     */
    public function create(Connection $connection) : Statement
    {
        try {
            $ret = $connection->prepare($this->sql);
            $ret->bind($this->values);
            return $ret;
        } catch (ModelReadException|ModelWriteException $ex) {
            throw new ModelOperationFailedException(previous: $ex);
        }
    }


    /**
     * Append a statement with given join character, if the statement is not empty
     * @param string $join
     * @param QueryStatement $statement
     * @return $this
     */
    public function appendJoinIfNotEmpty(string $join, self $statement) : static
    {
        if ($statement->isEmpty()) return $this;

        $this->sql .= $join;
        return $this->append($statement);
    }


    /**
     * Append a statement or SQL string
     * @param QueryStatement|string $statement
     * @return $this
     */
    public function append(self|string $statement) : static
    {
        if ($statement instanceof self) {
            // Append a statement
            $this->sql .= $statement->sql;
            $this->values = array_merge($this->values, $statement->values);
        } else {
            // Append a string
            $this->sql .= $statement;
        }

        return $this;
    }


    /**
     * If current statement is empty
     * @return bool
     */
    public function isEmpty() : bool
    {
        return $this->sql === '';
    }
}