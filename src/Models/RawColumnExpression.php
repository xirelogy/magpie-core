<?php

namespace Magpie\Models;

use Magpie\Models\Impls\QueryContext;
use Magpie\Models\Impls\QueryStatement;

/**
 * General column expression with raw statement
 */
class RawColumnExpression extends ColumnExpression
{
    /**
     * @var ColumnName|string The raw expression
     */
    public ColumnName|string $expr;


    /**
     * Constructor
     * @param ColumnName|string $expr
     */
    public function __construct(ColumnName|string $expr)
    {
        $this->expr = $expr;
    }


    /**
     * @inheritDoc
     */
    protected function onFinalize(QueryContext $context) : QueryStatement
    {
        if ($this->expr instanceof ColumnName) return $this->expr->_finalize($context);

        return new QueryStatement($this->expr);
    }
}