<?php

namespace Magpie\Models;

use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\InvalidStateException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Concepts\AttributeCastable;
use Magpie\Models\Concepts\Modelable;
use Magpie\Models\Concepts\QueryCommonAggregatable;
use Magpie\Models\Concepts\QueryFilterable;
use Magpie\Models\Concepts\QueryFilterApplicable;
use Magpie\Models\Concepts\QueryOrderable;
use Magpie\Models\Concepts\QuerySelectable;
use Magpie\Models\Enums\OrderType;
use Magpie\Models\Enums\WhereJoinType;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelWriteException;
use Magpie\Models\Exceptions\QuerySelectResetException;
use Magpie\Models\Impls\FilterApplyMode;
use Magpie\Models\Impls\ModelFinalizer;
use Magpie\Models\Impls\QueryContext;
use Magpie\Models\Impls\QueryOrder;
use Magpie\Models\Impls\QuerySetupListener;
use Magpie\Models\Impls\QueryStatement;
use Magpie\Models\Impls\SimpleQueryOrder;

/**
 * Database query
 * @template T
 */
abstract class Query extends BaseQueryConditionable implements QueryOrderable, QueryFilterable, QueryCommonAggregatable
{
    /**
     * Column name to hold aggregation value
     */
    protected const COL_NAME_AGG = 'agg';

    /**
     * @var QuerySetupListener|null Associated query setup listener
     */
    protected ?QuerySetupListener $listener;
    /**
     * @var array<QuerySelectable>|null Selected columns
     */
    protected array $selectedColumns = [];
    /**
     * @var array<QueryOrder> Sort orders
     */
    protected array $orders = [];
    /**
     * @var bool If the field selection had been reset
     */
    protected bool $isSelectReset = false;
    /**
     * @var QueryFilterApplicable|null Query filter
     */
    protected ?QueryFilterApplicable $filter = null;


    /**
     * Constructor
     * @param QuerySetupListener|null $listener
     * @param WhereJoinType $joinPrevious
     */
    protected function __construct(?QuerySetupListener $listener, WhereJoinType $joinPrevious = WhereJoinType::AND)
    {
        parent::__construct($joinPrevious);

        $this->listener = $listener;
    }


    /**
     * Select columns
     * @param QuerySelectable|string ...$columns
     * @return $this
     * @note This function will reset the selection scope and will cause the
     * expected return Model may have different fields than expected during
     * hydration
     */
    public function select(QuerySelectable|string ...$columns) : static
    {
        $this->isSelectReset = true;
        $this->selectedColumns = [];
        return $this->addSelect(...$columns);
    }


    /**
     * Add selected columns
     * @param QuerySelectable|string ...$columns
     * @return $this
     */
    public function addSelect(QuerySelectable|string ...$columns) : static
    {
        foreach ($columns as $column) {
            $this->selectedColumns[] = static::acceptSelect($column);
        }

        return $this;
    }


    /**
     * Accept column selection
     * @param QuerySelectable|string $column
     * @return QuerySelectable
     */
    protected static function acceptSelect(QuerySelectable|string $column) : QuerySelectable
    {
        if ($column instanceof QuerySelectable) return $column;

        return ColumnName::from($column);
    }


    /**
     * @inheritDoc
     */
    public function orderBy(string|ColumnName $column, OrderType $order = OrderType::ASC) : static
    {
        $this->orders[] = new SimpleQueryOrder($column, $order);
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function filterWith(QueryFilterApplicable $filter) : static
    {
        $this->filter = $filter;
        return $this;
    }


    /**
     * Query for multiple records
     * @return iterable<T|Modelable>
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public function list() : iterable
    {
        return $this->listUsingFilterMode(FilterApplyMode::YES);
    }


    /**
     * Query for multiple records (using specific filter mode)
     * @param FilterApplyMode $filterMode
     * @return iterable
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    private function listUsingFilterMode(FilterApplyMode $filterMode) : iterable
    {
        if ($this->isSelectReset) throw new QuerySelectResetException();

        $statement = $this->prepareSelectStatement($filterMode, $modelFinalizer);
        if (!$modelFinalizer instanceof ModelFinalizer) throw new InvalidStateException();

        // Purposely return generator from another function to ensure pre-actions are executed
        return $this->listFrom($statement, $modelFinalizer);
    }


    /**
     * Query for multiple records from given statement and finalizer
     * @param Statement $statement
     * @param ModelFinalizer $modelFinalizer
     * @return iterable<T|Modelable>
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    private function listFrom(Statement $statement, ModelFinalizer $modelFinalizer) : iterable
    {
        foreach ($statement->query() as $row) {
            yield $modelFinalizer->finalize($row);
        }
    }


    /**
     * Query for the first record
     * @return T|Modelable|null
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     * @noinspection PhpDocSignatureInspection
     */
    public function first() : ?Modelable
    {
        foreach ($this->listUsingFilterMode(FilterApplyMode::FIRST) as $result) {
            return $result;
        }

        return null;
    }


    /**
     * @inheritDoc
     */
    public final function aggregate(ColumnExpression $expr, ?string $cast = null) : mixed
    {
        return $this->onAggregate($expr, FilterApplyMode::YES, $cast);
    }


    /**
     * Handle aggregate query on single aggregation function
     * @param ColumnExpression $expr
     * @param FilterApplyMode $filterMode
     * @param class-string<AttributeCastable>|null $cast
     * @return mixed
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    protected function onAggregate(ColumnExpression $expr, FilterApplyMode $filterMode, ?string $cast) : mixed
    {
        $this->select(new ColumnExpressionSelect($expr, static::COL_NAME_AGG));

        $statement = $this->prepareSelectStatement($filterMode);
        foreach ($statement->query() as $row) {
            if (!array_key_exists(static::COL_NAME_AGG, $row)) return null;

            $value = $row[static::COL_NAME_AGG];
            if ($cast !== null) {
                if (!is_subclass_of($cast, AttributeCastable::class)) throw new ClassNotOfTypeException($cast, AttributeCastable::class);
                $value = $cast::fromDb(static::COL_NAME_AGG, $value);
            }

            return $value;
        }

        return null;
    }


    /**
     * @inheritDoc
     */
    public function count(ColumnName|string|null $columnName = null) : int
    {
        return $this->aggregate(ColumnExpression::count($columnName));
    }


    /**
     * @inheritDoc
     */
    public function sum(ColumnName|string $columnName) : int|float
    {
        return $this->aggregate(ColumnExpression::sum($columnName));
    }


    /**
     * @inheritDoc
     */
    public function avg(ColumnName|string $columnName) : int|float
    {
        return $this->aggregate(ColumnExpression::avg($columnName));
    }


    /**
     * @inheritDoc
     */
    public function min(ColumnName|string $columnName) : int|float
    {
        return $this->aggregate(ColumnExpression::min($columnName));
    }


    /**
     * @inheritDoc
     */
    public function max(ColumnName|string $columnName) : int|float
    {
        return $this->aggregate(ColumnExpression::max($columnName));
    }


    /**
     * Finalize current query as a sub-query
     * @param QueryContext $context
     * @return QueryStatement
     * @throws SafetyCommonException
     * @internal
     */
    public abstract function _subFinalize(QueryContext $context) : QueryStatement;


    /**
     * Prepare the 'SELECT' query statement
     * @param FilterApplyMode $filterMode Filter application mode
     * @param ModelFinalizer|null $modelFinalizer Finalizer to create corresponding return model
     * @return Statement
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    protected abstract function prepareSelectStatement(FilterApplyMode $filterMode, ?ModelFinalizer &$modelFinalizer = null) : Statement;


    /**
     * Duplicate from given RHS
     * @param Query $rhs
     * @param bool $isClearFilter
     * @return void
     */
    protected function duplicateFrom(self $rhs, bool $isClearFilter) : void
    {
        $this->selectedColumns = $rhs->selectedColumns;
        $this->orders = $rhs->orders;
        $this->isSelectReset = $rhs->isSelectReset;
        $this->condition = $rhs->condition;
        $this->filter = !$isClearFilter ? $rhs->filter : null;
    }
}