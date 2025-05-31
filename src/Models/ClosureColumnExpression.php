<?php

namespace Magpie\Models;

use Closure;
use Magpie\Models\Concepts\QueryContextServiceable;
use Magpie\Models\Impls\QueryContext;
use Magpie\Models\Impls\QueryStatement;

/**
 * Column expression constructed using closure
 */
class ClosureColumnExpression extends ColumnExpression
{
    /**
     * @var Closure(QueryContextServiceable):QueryStatementCreator
     */
    protected readonly Closure $fn;


    /**
     * Constructor
     * @param callable(QueryContextServiceable):QueryStatementCreator $fn
     */
    protected function __construct(callable $fn)
    {
        $this->fn = $fn;
    }


    /**
     * @inheritDoc
     */
    protected function onFinalize(QueryContext $context) : QueryStatement
    {
        $seed = ($this->fn)($context);

        return new QueryStatement($seed->sql, $seed->values);
    }


    /**
     * Create a new instance
     * @param callable(QueryContextServiceable):QueryStatementCreator $fn
     * @return static
     */
    public static function create(callable $fn) : static
    {
        return new static($fn);
    }
}