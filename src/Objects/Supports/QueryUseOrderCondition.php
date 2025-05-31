<?php

namespace Magpie\Objects\Supports;

use Magpie\Models\BaseQueryConditionable;

/**
 * Query condition with specific use-order
 */
class QueryUseOrderCondition extends QueryCondition
{
    /**
     * @var QueryCondition Base condition
     */
    public readonly QueryCondition $baseCondition;
    /**
     * @var QueryOrderCondition Order condition
     */
    public readonly QueryOrderCondition $orderCondition;


    /**
     * Constructor
     * @param QueryCondition $baseCondition
     * @param QueryOrderCondition $orderCondition
     */
    public function __construct(QueryCondition $baseCondition, QueryOrderCondition $orderCondition)
    {
        $this->baseCondition = $baseCondition;
        $this->orderCondition = $orderCondition;
    }


    /**
     * @inheritDoc
     */
    public final function applyOnQuery(BaseQueryConditionable $query) : void
    {
        $this->baseCondition->applyOnQuery($query);
    }
}