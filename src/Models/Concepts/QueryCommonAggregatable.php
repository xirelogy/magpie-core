<?php

namespace Magpie\Models\Concepts;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\ColumnName;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelWriteException;

/**
 * May aggregate using common SQL aggregates
 */
interface QueryCommonAggregatable extends QueryAggregatable
{
    /**
     * Aggregate query using count(): Count instance of entries
     * @param ColumnName|string|null $columnName
     * @return int
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public function count(ColumnName|string|null $columnName = null) : int;


    /**
     * Aggregate query using sum(): Sum up all values of given column
     * @param ColumnName|string $columnName
     * @return int|float
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public function sum(ColumnName|string $columnName) : int|float;


    /**
     * Aggregate query using avg(): Take average of the values of given column
     * @param ColumnName|string $columnName
     * @return int|float
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public function avg(ColumnName|string $columnName) : int|float;


    /**
     * Aggregate query using min(): Minimum of all values of given column
     * @param ColumnName|string $columnName
     * @return int|float
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public function min(ColumnName|string $columnName) : int|float;


    /**
     * Aggregate query using max(): Maximum of all values of given column
     * @param ColumnName|string $columnName
     * @return int|float
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public function max(ColumnName|string $columnName) : int|float;
}