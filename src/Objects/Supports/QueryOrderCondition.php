<?php

namespace Magpie\Objects\Supports;

use Magpie\General\Traits\StaticCreatable;
use Magpie\Models\BaseQueryConditionable;
use Magpie\Models\ColumnExpression;
use Magpie\Models\ColumnName;
use Magpie\Models\Concepts\QueryOrderable;
use Magpie\Models\Enums\OrderType;
use Magpie\Models\Query;

/**
 * A sort order query condition
 */
class QueryOrderCondition extends QueryCondition implements QueryOrderable
{
    use StaticCreatable;

    /**
     * @var array<QueryOrderSpecification> Sort orders
     */
    protected array $orders = [];


    /**
     * Constructor
     */
    protected function __construct()
    {

    }


    /**
     * @inheritDoc
     */
    public final function orderBy(string|ColumnName|ColumnExpression $column, OrderType $order = OrderType::ASC) : static
    {
        $this->orders[] = new QueryOrderSpecification($column, $order);

        return $this;
    }


    /**
     * @inheritDoc
     */
    public final function applyOnQuery(BaseQueryConditionable $query) : void
    {
        if (!$query instanceof Query) return;

        foreach ($this->orders as $order) {
            $query->orderBy($order->column, $order->order);
        }
    }
}