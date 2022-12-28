<?php

namespace Magpie\Models\Concepts;

use Magpie\Models\ColumnName;
use Magpie\Models\Enums\OrderType;

/**
 * May specify sorting order
 */
interface QueryOrderable
{
    /**
     * Specify the sort order
     * @param ColumnName|string $column
     * @param OrderType $order
     * @return $this
     */
    public function orderBy(ColumnName|string $column, OrderType $order = OrderType::ASC) : static;
}