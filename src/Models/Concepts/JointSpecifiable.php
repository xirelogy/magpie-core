<?php

namespace Magpie\Models\Concepts;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\ColumnName;
use Magpie\Models\Enums\WhereJoinType;

/**
 * May specify the joint condition
 */
interface JointSpecifiable
{
    /**
     * Specify the join condition between columns from both left/right database table
     * @param callable(JointSpecifiable):void|ColumnName|string $lhs
     *          The column on the left of the join clause,
     *          or a callable to define next level join clause (wrap in bracket)
     * @param ColumnName|string|null $rhs
     *          The column on the right of the join clause (for case of 2 arguments)
     * @param WhereJoinType $joinPrevious
     *          Join type to the previous clause
     * @return $this
     * @throws SafetyCommonException
     * @noinspection PhpDocSignatureInspection
     */
    public function on(callable|ColumnName|string $lhs, ColumnName|string|null $rhs = null, WhereJoinType $joinPrevious = WhereJoinType::AND) : static;


    /**
     * Specify the join condition between columns from both left/right database table (join to previous condition using OR)
     * @param callable(JointSpecifiable):void|ColumnName|string $lhs
     *          The column on the left of the join clause,
     *          or a callable to define next level join clause (wrap in bracket)
     * @param ColumnName|string|null $rhs
     *          The column on the right of the join clause (for case of 2 arguments)
     * @param WhereJoinType $joinPrevious
     *          Join type to the previous clause
     * @return $this
     * @throws SafetyCommonException
     * @noinspection PhpDocSignatureInspection
     */
    public function orOn(callable|ColumnName|string $lhs, ColumnName|string|null $rhs = null) : static;
}