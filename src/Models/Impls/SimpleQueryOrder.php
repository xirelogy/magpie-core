<?php

namespace Magpie\Models\Impls;

use Magpie\Models\ColumnName;
use Magpie\Models\Enums\OrderType;

/**
 * A simple query order
 * @internal
 */
class SimpleQueryOrder extends QueryOrder
{
    /**
     * @var ColumnName|string Column name
     */
    public readonly ColumnName|string $column;
    /**
     * @var OrderType Order type
     */
    public readonly OrderType $order;


    /**
     * Constructor
     * @param ColumnName|string $column
     * @param OrderType $order
     */
    public function __construct(ColumnName|string $column, OrderType $order)
    {
        $this->column = $column;
        $this->order = $order;
    }


    /**
     * @inheritDoc
     */
    public function _finalize(QueryContext $context) : QueryStatement
    {
        $sql = $context->getColumnNameSql($this->column) . ' ' . strtoupper($this->order->value);
        return new QueryStatement($sql);
    }
}