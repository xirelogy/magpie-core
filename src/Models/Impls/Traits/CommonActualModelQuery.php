<?php

namespace Magpie\Models\Impls\Traits;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Connection;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelWriteException;
use Magpie\Models\Filters\FragmentFilter;
use Magpie\Models\Impls\FilterApplyMode;
use Magpie\Models\Impls\QueryContext;
use Magpie\Models\Impls\QueryStatement;
use Magpie\Models\Statement;

/**
 * Support common features for ActualModelQuery
 */
trait CommonActualModelQuery
{
    use WithQueryFilterService;


    /**
     * Common part to prepare select statement
     * @param QueryStatement $query
     * @param QueryContext $context
     * @param Connection $connection
     * @param FilterApplyMode $filterMode
     * @return Statement
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    protected function commonPrepareSelectStatement(QueryStatement $query, QueryContext $context, Connection $connection, FilterApplyMode $filterMode) : Statement
    {
        $whereFinalized = $this->condition->_finalize($context);
        $query->appendJoinIfNotEmpty(' WHERE ', $whereFinalized);

        $ordersFinalized = $this->finalizeOrders($context);
        $query->appendJoinIfNotEmpty(' ORDER BY ', $ordersFinalized);

        if ($filterMode === FilterApplyMode::YES && $this->filter !== null) {
            $service = $this->createFilterService($query, $context);
            $this->filter->apply($service);
            $query = $service->modifyQueryStatement();
        } else if ($filterMode === FilterApplyMode::FIRST && $this->filter === null) {
            $service = $this->createFilterService($query, $context);
            FragmentFilter::limit(1)->apply($service);
            $query = $service->modifyQueryStatement();
        }

        return $query->create($connection);
    }


    /**
     * Finalize order conditions
     * @param QueryContext $context
     * @return QueryStatement
     * @throws SafetyCommonException
     */
    private function finalizeOrders(QueryContext $context) : QueryStatement
    {
        $ret = new QueryStatement('');

        if (count($this->orders) <= 0) return $ret;

        foreach ($this->orders as $order) {
            $ret->appendJoinIfNotEmpty(', ', $order->_finalize($context));
        }

        $ret->sql = substr($ret->sql, 2);
        return $ret;
    }
}