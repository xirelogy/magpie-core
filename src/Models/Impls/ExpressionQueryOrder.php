<?php

namespace Magpie\Models\Impls;

use Magpie\Models\ColumnExpression;
use Magpie\Models\Enums\OrderType;

/**
 * A column expression query order
 * @internal
 */
class ExpressionQueryOrder extends QueryOrder
{
    /**
     * @var ColumnExpression The expression
     */
    public readonly ColumnExpression $expr;
    /**
     * @var OrderType Order type
     */
    public readonly OrderType $order;


    /**
     * Constructor
     * @param ColumnExpression $expr
     * @param OrderType $order
     */
    public function __construct(ColumnExpression $expr, OrderType $order)
    {
        $this->expr = $expr;
        $this->order = $order;
    }


    /**
     * @inheritDoc
     */
    public function _finalize(QueryContext $context) : QueryStatement
    {
        return $this->expr->_finalize($context);
    }
}