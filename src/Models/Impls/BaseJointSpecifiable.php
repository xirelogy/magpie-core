<?php

namespace Magpie\Models\Impls;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\Models\ColumnName;
use Magpie\Models\Concepts\JointSpecifiable;
use Magpie\Models\Enums\WhereJoinType;
use Magpie\Models\Schemas\TableSchema;

/**
 * Common implementation for JointSpecifiable
 * @internal
 */
abstract class BaseJointSpecifiable implements JointSpecifiable
{
    /**
     * @var TableSchema Schema for the base table (of the entire definition)
     */
    protected readonly TableSchema $baseSchema;
    /**
     * @var TableSchema Schema for the table being joint
     */
    protected readonly TableSchema $jointSchema;
    /**
     * @var LogicalQueryCondition Joint clause
     */
    protected LogicalQueryCondition $clause;


    /**
     * Constructor
     * @param TableSchema $baseSchema
     * @param TableSchema $jointSchema
     * @param WhereJoinType $joinPrevious
     */
    protected function __construct(TableSchema $baseSchema, TableSchema $jointSchema, WhereJoinType $joinPrevious = WhereJoinType::AND)
    {
        $this->baseSchema = $baseSchema;
        $this->jointSchema = $jointSchema;
        $this->clause = new LogicalQueryCondition([], $joinPrevious);
    }


    /**
     * @inheritDoc
     */
    public function on(callable|ColumnName|string $lhs, ColumnName|string|null $rhs = null, WhereJoinType $joinPrevious = WhereJoinType::AND) : static
    {
        $this->clause->conditions[] = $this->_on($lhs, $rhs, $joinPrevious);

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function orOn(callable|string|ColumnName $lhs, string|ColumnName|null $rhs = null) : static
    {
        return $this->on($lhs, $rhs, WhereJoinType::OR);
    }


    /**
     * Process the arguments for on(), producing the corresponding condition
     * @param callable(JointSpecifiable):void|ColumnName|string $lhs
     * @param ColumnName|string|null $rhs
     * @param WhereJoinType $joinPrevious
     * @return QueryCondition
     * @throws SafetyCommonException
     */
    protected function _on(callable|ColumnName|string $lhs, ColumnName|string|null $rhs, WhereJoinType $joinPrevious) : QueryCondition
    {
        // Treated as next-level condition
        if (is_callable($lhs)) {
            return $this->_onCallable($lhs, $joinPrevious);
        }

        $lhsColumn = static::acceptColumnSpecification($lhs, $this->baseSchema);
        $rhsColumn = static::acceptColumnSpecification($rhs, $this->jointSchema);

        return new SpecificQueryCondition($lhsColumn, '=', $rhsColumn, $joinPrevious);
    }


    /**
     * Specify next-level join clause
     * @param callable(JointSpecifiable):void $fn
     * @param WhereJoinType $joinPrevious
     * @return QueryCondition
     */
    private function _onCallable(callable $fn, WhereJoinType $joinPrevious) : QueryCondition
    {
        $levelQuery = new class($this->baseSchema, $this->jointSchema, $joinPrevious) extends BaseJointSpecifiable {

        };

        $fn($levelQuery);

        return $levelQuery->clause;
    }


    /**
     * Accept column specification
     * @param mixed $target
     * @param TableSchema $refTable
     * @return ColumnName
     * @throws UnsupportedValueException
     */
    protected static function acceptColumnSpecification(mixed $target, TableSchema $refTable) : ColumnName
    {
        if ($target instanceof ColumnName) return $target;
        if (is_string($target)) return ColumnName::fromTable($refTable, $target);

        throw new UnsupportedValueException($target, _l('join condition'));
    }
}