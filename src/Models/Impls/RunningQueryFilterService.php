<?php

namespace Magpie\Models\Impls;

use Closure;
use Magpie\Models\Query;
use Magpie\Models\QueryFilterService;

/**
 * QueryFilterService that is currently running
 * @internal
 */
abstract class RunningQueryFilterService extends QueryFilterService
{
    /**
     * @var QueryStatement Current query statement
     */
    protected QueryStatement $query;
    /**
     * @var QueryContext Current query context
     */
    protected QueryContext $context;
    /**
     * @var Closure Query duplicator
     */
    protected Closure $queryDuplicateFn;
    /**
     * @var int|null Limit value set
     */
    protected ?int $setLimit = null;
    /**
     * @var int|null Offset value set
     */
    protected ?int $setOffset = null;


    /**
     * Constructor
     * @param QueryStatement $query
     * @param QueryContext $context
     * @param callable():Query $queryDuplicateFn
     */
    public function __construct(QueryStatement $query, QueryContext $context, callable $queryDuplicateFn)
    {
        $this->query = $query;
        $this->context = $context;
        $this->queryDuplicateFn = $queryDuplicateFn;
    }


    /**
     * @inheritDoc
     */
    public function duplicateQuery() : Query
    {
        return ($this->queryDuplicateFn)();
    }


    /**
     * @inheritDoc
     */
    public function setLimit(?int $value) : void
    {
        $this->setLimit = $value;
    }


    /**
     * @inheritDoc
     */
    public function setOffset(?int $value) : void
    {
        $this->setOffset = $value;
    }


    /**
     * Apply the set values to the query statement
     * @return QueryStatement
     */
    public abstract function modifyQueryStatement() : QueryStatement;
}