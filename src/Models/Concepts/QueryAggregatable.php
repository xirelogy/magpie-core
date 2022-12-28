<?php

namespace Magpie\Models\Concepts;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\ColumnExpression;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelWriteException;

/**
 * May aggregate to a single result
 */
interface QueryAggregatable
{
    /**
     * Aggregate query on single aggregation function
     * @param ColumnExpression $expr
     * @param class-string<AttributeCastable>|null $cast
     * @return mixed
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public function aggregate(ColumnExpression $expr, ?string $cast = null) : mixed;
}