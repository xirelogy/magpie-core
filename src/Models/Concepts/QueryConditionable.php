<?php

namespace Magpie\Models\Concepts;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\ColumnName;
use Magpie\Models\Enums\WhereJoinType;

/**
 * May specify query condition
 */
interface QueryConditionable
{
    /**
     * Specify query condition
     * @param callable(QueryConditionable):void|ColumnName|string $column
     *      The column in the condition when provided as a string/column,
     *      or a callable to define next level query condition (wrap in bracket)
     * @param mixed|null $operator
     *      Operator of the query provided as string/CommonOperator (for case of 3 arguments),
     *      or the condition value with a 'equals to' operator
     * @param mixed|null $value
     *      Condition value
     * @param WhereJoinType $joinPrevious
     *      Join type to the previous condition
     * @return $this
     * @throws SafetyCommonException
     */
    public function where(callable|ColumnName|string $column, mixed $operator = null, mixed $value = null, WhereJoinType $joinPrevious = WhereJoinType::AND) : static;


    /**
     * Specify query condition to be not met
     * @param callable(QueryConditionable):void|ColumnName|string $column
     *      The column in the condition when provided as a string/column,
     *      or a callable to define next level query condition (wrap in bracket)
     * @param mixed|null $operator
     *      Operator of the query provided as string/CommonOperator (for case of 3 arguments),
     *      or the condition value with a 'equals to' operator
     * @param mixed|null $value
     *      Condition value
     * @param WhereJoinType $joinPrevious
     *      Join type to the previous condition
     * @return $this
     * @throws SafetyCommonException
     */
    public function whereNot(callable|ColumnName|string $column, mixed $operator = null, mixed $value = null, WhereJoinType $joinPrevious = WhereJoinType::AND) : static;


    /**
     * Specify query condition (join to previous condition using OR)
     * @param callable(QueryConditionable):void|ColumnName|string $column
     *      The column in the condition when provided as a string/column,
     *      or a callable to define next level query condition (wrap in bracket)
     * @param mixed|null $operator
     *      Operator of the query provided as string/CommonOperator (for case of 3 arguments),
     *      or the condition value with a 'equals to' operator
     * @param mixed|null $value
     *      Condition value
     * @return $this
     * @throws SafetyCommonException
     */
    public function orWhere(callable|ColumnName|string $column, mixed $operator = null, mixed $value = null) : static;



    /**
     * Specify query condition to be not met (join to previous condition using OR)
     * @param callable(QueryConditionable):void|ColumnName|string $column
     *      The column in the condition when provided as a string/column,
     *      or a callable to define next level query condition (wrap in bracket)
     * @param mixed|null $operator
     *      Operator of the query provided as string/CommonOperator (for case of 3 arguments),
     *      or the condition value with a 'equals to' operator
     * @param mixed|null $value
     *      Condition value
     * @return $this
     * @throws SafetyCommonException
     */
    public function orWhereNot(callable|ColumnName|string $column, mixed $operator = null, mixed $value = null) : static;
}