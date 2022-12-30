<?php

namespace Magpie\Models;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\Models\Concepts\QueryConditionable;
use Magpie\Models\Enums\CommonOperator;
use Magpie\Models\Enums\WhereJoinType;
use Magpie\Models\Impls\LogicalQueryCondition;
use Magpie\Models\Impls\NegatedQueryCondition;
use Magpie\Models\Impls\QueryCondition;
use Magpie\Models\Impls\SpecificQueryCondition;

/**
 * Common implementation for QueryConditionable
 */
abstract class BaseQueryConditionable implements QueryConditionable
{
    /**
     * @var LogicalQueryCondition All query conditions
     */
    protected LogicalQueryCondition $condition;


    /**
     * Constructor
     * @param WhereJoinType $joinPrevious
     */
    protected function __construct(WhereJoinType $joinPrevious = WhereJoinType::AND)
    {
        $this->condition = new LogicalQueryCondition([], $joinPrevious);
    }


    /**
     * @inheritDoc
     */
    public function where(callable|ColumnName|string $column, mixed $operator = null, mixed $value = null, WhereJoinType $joinPrevious = WhereJoinType::AND) : static
    {
        $this->condition->conditions[] = $this->_where($column, $operator, $value, $joinPrevious);

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function whereNot(callable|ColumnName|string $column, mixed $operator = null, mixed $value = null, WhereJoinType $joinPrevious = WhereJoinType::AND) : static
    {
        $this->condition->conditions[] = $this->_whereNot($column, $operator, $value, $joinPrevious);

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function orWhere(callable|ColumnName|string $column, mixed $operator = null, mixed $value = null) : static
    {
        return $this->where($column, $operator, $value, WhereJoinType::OR);
    }


    /**
     * @inheritDoc
     */
    public function orWhereNot(callable|string|ColumnName $column, mixed $operator = null, mixed $value = null) : static
    {
        return $this->whereNot($column, $operator, $value, WhereJoinType::OR);
    }


    /**
     * Check if the target is a column specification
     * @param mixed $target
     * @return bool
     */
    protected static function isColumnSpecification(mixed $target) : bool
    {
        if ($target instanceof ColumnName) return true;
        if (is_string($target)) return true;

        return false;
    }


    /**
     * Process the arguments for where(), producing the corresponding negated condition
     * @param callable|ColumnName|string $column
     * @param mixed $operator
     * @param mixed $value
     * @param WhereJoinType $joinPrevious
     * @return QueryCondition
     * @throws SafetyCommonException
     */
    private function _whereNot(callable|ColumnName|string $column, mixed $operator, mixed $value, WhereJoinType $joinPrevious) : QueryCondition
    {
        $baseCondition = $this->_where($column, $operator, $value, WhereJoinType::AND);

        if ($baseCondition instanceof SpecificQueryCondition) {
            return new SpecificQueryCondition(
                $baseCondition->columnName,
                CommonOperator::negate($baseCondition->operator)->value,
                $baseCondition->value,
                $joinPrevious,
            );
        }

        return new NegatedQueryCondition($baseCondition, $joinPrevious);
    }


    /**
     * Process the arguments for where(), producing the corresponding condition
     * @param callable|ColumnName|string $column
     * @param mixed $operator
     * @param mixed $value
     * @param WhereJoinType $joinPrevious
     * @return QueryCondition
     * @throws SafetyCommonException
     */
    private function _where(callable|ColumnName|string $column, mixed $operator, mixed $value, WhereJoinType $joinPrevious) : QueryCondition
    {
        // Treated as next-level condition
        if (is_callable($column)) {
            return $this->_whereCallable($column, $joinPrevious);
        }

        if (!static::isColumnSpecification($column)) throw new UnsupportedValueException($column, _l('query condition'));

        // Try to assume the operator whenever necessary
        if ($operator === null) {
            return $this->_whereSpecific($column, CommonOperator::EQUAL, $value, $joinPrevious);
        }

        // When there is no value, check operand
        if ($value === null) {
            if ($operator === '=' || $operator === '<>' || $operator === '!=') {
                return $this->_whereSpecific($column, $operator, null, $joinPrevious);
            } else {
                return $this->_whereSpecific($column, CommonOperator::EQUAL, $operator, $joinPrevious);
            }
        }

        return $this->_whereSpecific($column, $operator, $value, $joinPrevious);
    }


    /**
     * Specify next-level query condition
     * @param callable(QueryConditionable):void $fn
     * @param WhereJoinType $joinPrevious
     * @return QueryCondition
     * @internal
     */
    private function _whereCallable(callable $fn, WhereJoinType $joinPrevious) : QueryCondition
    {
        $levelQuery = new class($joinPrevious) extends BaseQueryConditionable {

        };

        $fn($levelQuery);

        return $levelQuery->condition;
    }


    /**
     * Specify specific query condition
     * @param string|ColumnName $column
     * @param CommonOperator|string $operator
     * @param mixed $value
     * @param WhereJoinType $joinPrevious
     * @return QueryCondition
     * @throws SafetyCommonException
     * @internal
     */
    private function _whereSpecific(string|ColumnName $column, CommonOperator|string $operator, mixed $value, WhereJoinType $joinPrevious) : QueryCondition
    {
        if ($operator instanceof CommonOperator) $operator = $operator->value;

        return new SpecificQueryCondition($column, $operator, $value, $joinPrevious);
    }


    /**
     * A filler to explicitly mean the 'null' value used in query condition
     * @return mixed
     */
    public static final function null() : mixed
    {
        return null;
    }


    /**
     * Escape a like string for query condition
     * @param string $value
     * @return string
     */
    public static function escapeLikeString(string $value) : string
    {
        $ret = '';
        $length = strlen($value);

        for ($i = 0; $i < $length; ++$i) {
            $c = substr($value, $i, 1);
            $ret .= match ($c) {
                '_' => '\\_',
                '%' => '\\%',
                default => $c,
            };
        }

        return $ret;
    }
}