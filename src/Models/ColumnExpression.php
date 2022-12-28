<?php

namespace Magpie\Models;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Concepts\QueryArgumentable;
use Magpie\Models\Impls\QueryContext;
use Magpie\Models\Impls\QueryStatement;

/**
 * Column expression
 */
abstract class ColumnExpression implements QueryArgumentable
{
    /**
     * Take current expression as a selection
     * @param string|null $alias
     * @param string|null $cast
     * @return ColumnExpressionSelect
     */
    public final function select(?string $alias = null, ?string $cast = null) : ColumnExpressionSelect
    {
        return new ColumnExpressionSelect($this, $alias, $cast);
    }


    /**
     * @inheritDoc
     * @internal
     */
    public final function _finalize(QueryContext $context) : QueryStatement
    {
        return $this->onFinalize($context);
    }


    /**
     * Finalize for SQL query
     * @param QueryContext $context
     * @return QueryStatement
     * @throws SafetyCommonException
     */
    protected abstract function onFinalize(QueryContext $context) : QueryStatement;


    /**
     * Raw column expression
     * @param ColumnName|string $expr
     * @return self
     */
    public static function raw(ColumnName|string $expr) : self
    {
        return new RawColumnExpression($expr);
    }


    /**
     * Function column expression
     * @param string $name
     * @param mixed ...$arguments
     * @return static
     */
    public static function function(string $name, mixed ...$arguments) : self
    {
        return new FunctionColumnExpression($name, ...$arguments);
    }


    /**
     * count(): Count instance of entries
     * @param ColumnName|string|null $columnName
     * @return static
     */
    public static function count(ColumnName|string|null $columnName = null) : self
    {
        $columnName = static::acceptColumnName($columnName ?? '*');
        return static::function('count', $columnName);
    }


    /**
     * sum(): Sum up all values of given column
     * @param ColumnName|string $columnName
     * @return static
     */
    public static function sum(ColumnName|string $columnName) : self
    {
        $columnName = static::acceptColumnName($columnName);
        return static::function('sum', $columnName);
    }


    /**
     * avg(): Take average of the values of given column
     * @param ColumnName|string $columnName
     * @return static
     */
    public static function avg(ColumnName|string $columnName) : self
    {
        $columnName = static::acceptColumnName($columnName);
        return static::function('avg', $columnName);
    }


    /**
     * min(): Minimum of all values of given column
     * @param ColumnName|string $columnName
     * @return static
     */
    public static function min(ColumnName|string $columnName) : self
    {
        $columnName = static::acceptColumnName($columnName);
        return static::function('min', $columnName);
    }


    /**
     * max(): Maximum of all values of given column
     * @param ColumnName|string $columnName
     * @return static
     */
    public static function max(ColumnName|string $columnName) : self
    {
        $columnName = static::acceptColumnName($columnName);
        return static::function('max', $columnName);
    }


    /**
     * Accept column name
     * @param ColumnName|string $columnName
     * @return ColumnName
     */
    protected static function acceptColumnName(ColumnName|string $columnName) : ColumnName
    {
        if ($columnName instanceof ColumnName) return $columnName;

        return ColumnName::from($columnName);
    }
}