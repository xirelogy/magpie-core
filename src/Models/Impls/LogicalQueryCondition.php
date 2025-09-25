<?php

namespace Magpie\Models\Impls;

use Magpie\General\Sugars\Quote;
use Magpie\Models\Enums\WhereJoinType;

/**
 * Multiple query conditions chained together in logical relationship
 * @internal
 */
class LogicalQueryCondition extends QueryCondition
{
    /**
     * @var array<QueryCondition> Chained conditions
     */
    public array $conditions;


    /**
     * Constructor
     * @param array<QueryCondition> $conditions
     * @param WhereJoinType $joinPrevious
     */
    public function __construct(array $conditions, WhereJoinType $joinPrevious)
    {
        parent::__construct($joinPrevious);

        $this->conditions = $conditions;
    }


    /**
     * @inheritDoc
     */
    public function isCompound() : bool
    {
        return count($this->conditions) > 1;
    }


    /**
     * @inheritDoc
     */
    public function _finalize(QueryContext $context) : QueryStatement
    {
        $ret = new QueryStatement('');

        foreach ($this->conditions as $condition) {
            $conditionStatement = $condition->_finalize($context);
            if ($condition->isCompound()) $conditionStatement->sql = Quote::bracket($conditionStatement->sql);
            if ($ret->isEmpty()) {
                $ret = $conditionStatement;
            } else {
                $ret->appendJoinIfNotEmpty(' ' . strtoupper($condition->joinPrevious->value) . ' ', $conditionStatement);
            }
        }

        return $ret;
    }
}