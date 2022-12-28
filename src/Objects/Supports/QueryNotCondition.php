<?php

namespace Magpie\Objects\Supports;

use Magpie\Models\BaseQueryConditionable;

/**
 * Representation of a negated query condition
 */
class QueryNotCondition extends QueryCondition
{
    /**
     * @var QueryCondition Target condition
     */
    protected QueryCondition $targetCondition;


    /**
     * Constructor
     * @param QueryCondition $targetCondition
     */
    public function __construct(QueryCondition $targetCondition)
    {
        $this->targetCondition = $targetCondition;
    }


    /**
     * @inheritDoc
     */
    public function applyOnQuery(BaseQueryConditionable $query) : void
    {
        $query->whereNot(function(BaseQueryConditionable $subQuery) : void {
            $this->targetCondition->applyOnQuery($subQuery);
        });
    }
}