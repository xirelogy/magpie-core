<?php

namespace Magpie\Objects\Supports;

use Magpie\Models\ColumnExpression;
use Magpie\Models\ColumnName;
use Magpie\Models\Enums\OrderType;

/**
 * Single query order specification
 */
class QueryOrderSpecification
{
    /**
     * @var string|ColumnName|ColumnExpression Column specification
     */
    public readonly string|ColumnName|ColumnExpression $column;
    /**
     * @var OrderType Order type
     */
    public readonly OrderType $order;


    /**
     * Constructor
     * @param string|ColumnName|ColumnExpression $column
     * @param OrderType $order
     */
    public function __construct(string|ColumnName|ColumnExpression $column, OrderType $order)
    {
        $this->column = $column;
        $this->order = $order;
    }
}