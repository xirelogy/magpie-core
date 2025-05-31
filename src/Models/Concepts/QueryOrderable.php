<?php

namespace Magpie\Models\Concepts;

use Magpie\Models\ColumnExpression;
use Magpie\Models\ColumnName;
use Magpie\Models\Enums\OrderType;

/**
 * May specify sorting order
 */
interface QueryOrderable
{
    /**
     * Specify the sort order
     * @param ColumnExpression|ColumnName|string $column
     * @param OrderType $order
     * @return $this
     */
    public function orderBy(ColumnExpression|ColumnName|string $column, OrderType $order = OrderType::ASC) : static;
}