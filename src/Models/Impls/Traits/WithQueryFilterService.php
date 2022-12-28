<?php

namespace Magpie\Models\Impls\Traits;

use Magpie\Models\Impls\QueryContext;
use Magpie\Models\Impls\QueryStatement;
use Magpie\Models\Impls\RunningQueryFilterService;

/**
 * Support for creating query's filter service
 * @internal
 */
trait WithQueryFilterService
{
    /**
     * Create service for query filter
     * @param QueryStatement $query
     * @param QueryContext $context
     * @return RunningQueryFilterService
     */
    private function createFilterService(QueryStatement $query, QueryContext $context) : RunningQueryFilterService
    {
        $queryDuplicateFn = function() : static {
            return $this->duplicateQuery(true);
        };

        return new class($query, $context, $queryDuplicateFn) extends RunningQueryFilterService {
            /**
             * @inheritDoc
             */
            public function modifyQueryStatement() : QueryStatement
            {
                $grammar = $this->context->grammar;
                if ($grammar === null) return $this->query;

                $retSql = $this->query->sql;

                if ($this->setLimit !== null) {
                    $retSql = $grammar->applyLimit($retSql, $this->setLimit);
                }

                if ($this->setOffset !== null) {
                    $retSql = $grammar->applyOffset($retSql, $this->setOffset);
                }

                return new QueryStatement($retSql, $this->query->values);
            }
        };
    }


    /**
     * Duplicate the current query
     * @param bool $isClearFilter
     * @return static
     */
    protected abstract function duplicateQuery(bool $isClearFilter) : static;
}
