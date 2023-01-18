<?php

namespace Magpie\Models\Impls;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\General\Sugars\Quote;
use Magpie\Models\ColumnName;
use Magpie\Models\Concepts\QueryArgumentable;
use Magpie\Models\Enums\CommonOperator;
use Magpie\Models\Enums\WhereJoinType;
use Magpie\Models\Query;
use Magpie\Models\Schemas\ColumnSchema;

/**
 * Specific query condition (column, operand, value)
 * @internal
 */
class SpecificQueryCondition extends QueryCondition
{
    /**
     * @var QueryArgumentable Corresponding column name (LHS)
     */
    public readonly QueryArgumentable $columnName;
    /**
     * @var CommonOperator Operator
     */
    public readonly CommonOperator $operator;
    /**
     * @var mixed Corresponding value (RHS)
     */
    public readonly mixed $value;


    /**
     * Constructor
     * @param string|ColumnName $columnName
     * @param string $operator
     * @param mixed $value
     * @param WhereJoinType $joinPrevious
     * @throws UnsupportedException
     */
    public function __construct(string|ColumnName $columnName, string $operator, mixed $value, WhereJoinType $joinPrevious)
    {
        parent::__construct($joinPrevious);

        $this->columnName = static::acceptColumnName($columnName);
        $this->operator = static::acceptOperator($operator);
        $this->value = $value;
    }


    /**
     * @inheritDoc
     */
    public function isCompound() : bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    public function _finalize(QueryContext $context) : QueryStatement
    {
        $ret = $this->columnName->_finalize($context);

        // Special handling for NULL
        if ($this->value === null) {
            switch ($this->operator) {
                case CommonOperator::EQUAL:
                    return $ret->append(' IS NULL');
                case CommonOperator::NOT_EQUAL:
                    return $ret->append(' IS NOT NULL');
                case CommonOperator::IN:
                case CommonOperator::NOT_IN:
                    throw new UnsupportedValueException($this->value, _l('`in` query condition value'));
                default:
                    return $ret->append(' ' . strtoupper($this->operator->value) . ' NULL');
            }
        }

        // Include the operator
        $ret->sql .= ' ' . strtoupper($this->operator->value);

        // Handle RHS as argument when necessary
        if ($this->value instanceof QueryArgumentable) {
            return $ret->append($this->value->_finalize($context));
        }

        // Otherwise, apply proper formatting according to schema
        $columnSchema = $context->getColumnSchema($this->columnName);

        // 'in' operator expect multiple values
        if ($this->operator == CommonOperator::IN || $this->operator == CommonOperator::NOT_IN) {
            if ($this->value instanceof Query) {
                // Sub-query
                $subFinalized = $this->value->_subFinalize($context);
                $subFinalized->sql = ' ' . Quote::bracket($subFinalized->sql);
                return $ret->append($subFinalized);
            } else if (is_array($this->value)) {
                // Expecting array of values
                $values = static::finalizeValues($this->value, $columnSchema);
                $placeholders = str_repeat('?, ', count($values));
                $sql = ' ' . Quote::bracket(substr($placeholders, 0, -2));
                return $ret->append(new QueryStatement($sql, $values));
            } else {
                // Not supported
                throw new UnsupportedValueException($this->value, _l('`in` query condition value'));
            }
        }

        // Single value expected
        return $ret->append(new QueryStatement(' ?', [static::finalizeValue($this->value, $columnSchema)]));
    }


    /**
     * Finalize the values
     * @param array $values
     * @param ColumnSchema|null $columnSchema
     * @return array
     * @throws SafetyCommonException
     */
    protected static function finalizeValues(array $values, ?ColumnSchema $columnSchema) : array
    {
        $ret = [];
        foreach ($values as $value) {
            $ret[] = static::finalizeValue($value, $columnSchema);
        }

        return $ret;
    }


    /**
     * Finalize value
     * @param mixed $value
     * @param ColumnSchema|null $columnSchema
     * @return mixed
     * @throws SafetyCommonException
     */
    protected static function finalizeValue(mixed $value, ?ColumnSchema $columnSchema) : mixed
    {
        if ($columnSchema !== null) {
            return $columnSchema->toDb($value);
        } else {
            return $value;
        }
    }


    /**
     * Accept column name
     * @param string|QueryArgumentable $name
     * @return QueryArgumentable
     */
    protected static function acceptColumnName(string|QueryArgumentable $name) : QueryArgumentable
    {
        if ($name instanceof QueryArgumentable) return $name;

        return ColumnName::from($name);
    }


    /**
     * Accept operator
     * @param string $operator
     * @return CommonOperator
     * @throws UnsupportedException
     */
    protected static function acceptOperator(string $operator) : CommonOperator
    {
        return match (strtolower(trim($operator))) {
            '=', '==' => CommonOperator::EQUAL,
            '<>', '!=' => CommonOperator::NOT_EQUAL,
            '<' => CommonOperator::LESS_THAN,
            '<=', '=<' => CommonOperator::LESS_THAN_EQUAL,
            '>' => CommonOperator::GREATER_THAN,
            '>=', '=>' => CommonOperator::GREATER_THAN_EQUAL,
            'like' => CommonOperator::LIKE,
            'not like' => CommonOperator::NOT_LIKE,
            'in' => CommonOperator::IN,
            'not in' => CommonOperator::NOT_IN,
            default => throw new UnsupportedValueException($operator, _l('query condition operator')),
        };
    }
}