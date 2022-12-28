<?php

namespace Magpie\Models;

use Magpie\Models\Concepts\AttributeCastable;
use Magpie\Models\Concepts\QuerySelectable;
use Magpie\Models\Impls\QueryContext;
use Magpie\Models\Impls\QueryStatement;

/**
 * Select a column expressed as expression
 */
class ColumnExpressionSelect implements QuerySelectable
{
    /**
     * @var ColumnExpression Associated column expression
     */
    public readonly ColumnExpression $expr;
    /**
     * @var string|null Aliased column name
     */
    public ?string $alias;
    /**
     * @var class-string<AttributeCastable>|null
     */
    public ?string $cast;


    /**
     * Constructor
     * @param ColumnExpression|ColumnName|string $expr
     * @param string|null $alias
     * @param class-string<AttributeCastable>|null $cast
     */
    public function __construct(ColumnExpression|ColumnName|string $expr, ?string $alias = null, ?string $cast = null)
    {
        $this->expr = static::acceptExpression($expr);
        $this->alias = $alias;
        $this->cast = $cast;
    }


    /**
     * @inheritDoc
     * @internal
     */
    public function _finalize(QueryContext $context) : QueryStatement
    {
        $exprStmt = $this->expr->_finalize($context);

        if ($this->alias === null) return $exprStmt;

        if ($this->cast !== null) $context->modelFinalizer?->addCast($this->alias, $this->cast);

        $sql = $exprStmt->sql . ' AS ' . $context->getColumnNameSql($this->alias);
        return new QueryStatement($sql, $exprStmt->values);
    }


    /**
     * Accept column expression
     * @param ColumnExpression|ColumnName|string $expr
     * @return ColumnExpression
     */
    protected static function acceptExpression(ColumnExpression|ColumnName|string $expr) : ColumnExpression
    {
        if ($expr instanceof ColumnExpression) return $expr;

        return ColumnExpression::raw($expr);
    }
}