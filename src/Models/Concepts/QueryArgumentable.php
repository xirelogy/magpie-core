<?php

namespace Magpie\Models\Concepts;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Impls\QueryContext;
use Magpie\Models\Impls\QueryStatement;

/**
 * May be used as a query argument
 */
interface QueryArgumentable
{
    /**
     * Finalize for SQL query
     * @param QueryContext $context
     * @return QueryStatement
     * @throws SafetyCommonException
     * @internal
     */
    public function _finalize(QueryContext $context) : QueryStatement;
}