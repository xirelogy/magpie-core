<?php

namespace Magpie\Models\Impls;

use Magpie\Models\Concepts\QueryArgumentable;
use Magpie\Models\Enums\WhereJoinType;

/**
 * Representation of a SQL query condition
 * @internal
 */
abstract class QueryCondition implements QueryArgumentable
{
    /**
     * @var WhereJoinType Join type to the previous condition
     */
    public WhereJoinType $joinPrevious;


    /**
     * Constructor
     * @param WhereJoinType $joinPrevious
     */
    public function __construct(WhereJoinType $joinPrevious)
    {
        $this->joinPrevious = $joinPrevious;
    }


    /**
     * If current condition is a compound condition
     * @return bool
     */
    public abstract function isCompound() : bool;
}