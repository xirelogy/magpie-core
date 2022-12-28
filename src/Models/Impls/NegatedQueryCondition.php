<?php

namespace Magpie\Models\Impls;

use Magpie\General\Sugars\Quote;
use Magpie\Models\Enums\WhereJoinType;

/**
 * Negated query condition (with NOT prefix)
 * @internal
 */
class NegatedQueryCondition extends QueryCondition
{
    /**
     * @var QueryCondition Contained condition
     */
    public readonly QueryCondition $subCondition;


    /**
     * Constructor
     * @param QueryCondition $subCondition
     * @param WhereJoinType $joinPrevious
     */
    public function __construct(QueryCondition $subCondition, WhereJoinType $joinPrevious)
    {
        parent::__construct($joinPrevious);

        $this->subCondition = $subCondition;
    }


    /**
     * @inheritDoc
     */
    public function isCompound() : bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    public function _finalize(QueryContext $context) : QueryStatement
    {
        $ret = $this->subCondition->_finalize($context);

        if ($this->subCondition->isCompound()) {
            $ret->sql = 'NOT ' . Quote::bracket($ret->sql);
        } else {
            $ret->sql = 'NOT ' . $ret->sql;
        }

        return $ret;
    }
}