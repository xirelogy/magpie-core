<?php

namespace Magpie\Models\Impls;

use Carbon\Carbon;
use Closure;
use Magpie\Exceptions\InvalidStateException;
use Magpie\Models\AllColumns;
use Magpie\Models\Concepts\ConnectionResolvable;
use Magpie\Models\Concepts\QueryArgumentable;
use Magpie\Models\Connection;
use Magpie\Models\Impls\Traits\CommonActualModelQuery;
use Magpie\Models\Impls\Traits\WithQueryFilterService;
use Magpie\Models\Model;
use Magpie\Models\ModelQuery;
use Magpie\Models\Schemas\TableSchema;
use Magpie\Models\Statement;

/**
 * Query implementation for model
 * @internal
 */
class ActualModelQuery extends ModelQuery
{
    use WithQueryFilterService;
    use CommonActualModelQuery;


    /**
     * @var ConnectionResolvable|string Model connection
     */
    protected ConnectionResolvable|string $connection;
    /**
     * @var TableSchema Associated table schema
     */
    protected TableSchema $tableSchema;
    /**
     * @var Closure Hydration function
     */
    protected Closure $hydrationFn;


    /**
     * Constructor
     * @param ConnectionResolvable|string $connection
     * @param TableSchema $tableSchema
     * @param callable(array,array):Model $hydrationFn
     * @param QuerySetupListener|null $listener
     */
    public function __construct(ConnectionResolvable|string $connection, TableSchema $tableSchema, callable $hydrationFn, ?QuerySetupListener $listener)
    {
        parent::__construct($listener);

        $this->connection = $connection;
        $this->tableSchema = $tableSchema;
        $this->hydrationFn = $hydrationFn;
        $this->selectedColumns = [
            static::acceptSelect(AllColumns::for($tableSchema)),
        ];
    }


    /**
     * @inheritDoc
     * @noinspection SqlNoDataSourceInspection
     * @noinspection SqlDialectInspection
     */
    public function _subFinalize(QueryContext $context) : QueryStatement
    {
        if ($context->connection === null) throw new InvalidStateException();

        $innerContext = new QueryContext($context->connection, $this->tableSchema);

        $selectionFinalized = $this->finalizeSelects($innerContext);

        $selectionSql = $selectionFinalized->sql;
        if ($this->isSelectDistinct) $selectionSql = 'DISTINCT ' . $selectionSql;

        $query = new QueryStatement('SELECT ' . $selectionSql . ' FROM ' . $this->formatTable($innerContext->connection), $selectionFinalized->values);

        $whereFinalized = $this->condition->_finalize($innerContext);
        $query->appendJoinIfNotEmpty(' WHERE ', $whereFinalized);

        return $query;
    }


    /**
     * @inheritDoc
     * @noinspection SqlNoDataSourceInspection
     * @noinspection SqlDialectInspection
     */
    protected function prepareSelectStatement(FilterApplyMode $filterMode, ?ModelFinalizer &$modelFinalizer = null) : Statement
    {
        $connection = Connection::from($this->connection);
        $context = new QueryContext($connection, $this->tableSchema);

        $modelFinalizer = ClosureModelFinalizer::create(function(array $values, array $casts) : Model {
            return ($this->hydrationFn)($values, $casts);
        });

        $selectionFinalized = $this->finalizeSelects($context, $modelFinalizer);

        $selectionSql = $selectionFinalized->sql;
        if ($this->isSelectDistinct) $selectionSql = 'DISTINCT ' . $selectionSql;

        $query = new QueryStatement('SELECT ' . $selectionSql . ' FROM ' . $this->formatTable($connection), $selectionFinalized->values);

        return $this->commonPrepareSelectStatement($query, $context, $connection, $filterMode);
    }


    /**
     * @inheritDoc
     */
    protected function duplicateQuery(bool $isClearFilter) : static
    {
        $ret = new static($this->connection, $this->tableSchema, $this->hydrationFn, null);
        $ret->duplicateFrom($this, $isClearFilter);
        return $ret;
    }


    /**
     * @inheritDoc
     * @noinspection SqlNoDataSourceInspection
     * @noinspection SqlDialectInspection
     */
    protected function prepareUpdateStatement(array $assignments) : Statement
    {
        $connection = Connection::from($this->connection);
        $context = new QueryContext($connection, $this->tableSchema);

        // Append update timestamp if needed
        $updateColumnSchema = $this->tableSchema->getUpdateTimestampColumn();
        if ($updateColumnSchema !== null) {
            $updateColumnName = $updateColumnSchema->getName();
            if (!array_key_exists($updateColumnName, $assignments)) {
                $assignments[$updateColumnName] = PatchHost::tryUpdateTimestamp($this->tableSchema->getModelClassName()) ?? Carbon::now();
            }
        }

        // Build the query
        $query = new QueryStatement('UPDATE ' . $this->formatTable($connection) . ' SET ');

        // Process the assignments
        $isFirstAssign = true;
        foreach ($assignments as $assignKey => $assignValue) {
            if ($isFirstAssign) {
                $isFirstAssign = false;
            } else {
                $query->sql .= ', ';
            }

            $query->sql .= $context->getColumnNameSql($assignKey) . ' = ';

            if ($assignValue instanceof QueryArgumentable) {
                // Handle query arguments differently
                $query->append($assignValue->_finalize($context));
            } else {
                // Handle like a value
                $assignColumnSchema = $context->getColumnSchema($assignKey);
                $query->sql .= '?';
                $query->values[] = $assignColumnSchema !== null ? $assignColumnSchema->toDb($assignValue, $connection) : $assignValue;
            }
        }

        // Add the conditions
        $whereFinalized = $this->condition->_finalize($context);
        $query->appendJoinIfNotEmpty(' WHERE ', $whereFinalized);

        // Notify to listeners
        $this->listener?->notifyUpdateAttributes($assignments);

        return $query->create($connection);
    }


    /**
     * @inheritDoc
     * @noinspection SqlNoDataSourceInspection
     * @noinspection SqlDialectInspection
     */
    protected function prepareDeleteStatement() : Statement
    {
        $connection = Connection::from($this->connection);
        $context = new QueryContext($connection, $this->tableSchema);

        $query = new QueryStatement('DELETE FROM ' . $this->formatTable($connection));

        // Add the conditions
        $whereFinalized = $this->condition->_finalize($context);
        $query->appendJoinIfNotEmpty(' WHERE ', $whereFinalized);

        return $query->create($connection);
    }


    /**
     * Format the table name
     * @param Connection $connection
     * @return string
     */
    private function formatTable(Connection $connection) : string
    {
        _used($connection);

        return SqlFormat::backTick($this->tableSchema->getName());
    }


    /**
     * Finalize selections
     * @param QueryContext $context
     * @param ModelFinalizer|null $modelFinalizer
     * @return QueryStatement
     */
    private function finalizeSelects(QueryContext $context, ?ModelFinalizer $modelFinalizer = null) : QueryStatement
    {
        try {
            $context->modelFinalizer = $modelFinalizer;

            if (count($this->selectedColumns) <= 0) {
                $modelFinalizer?->markAllColumnsSelected();
                return new QueryStatement('*');
            }

            $ret = new QueryStatement('');
            foreach ($this->selectedColumns as $selectedColumn) {
                $ret->appendJoinIfNotEmpty(', ', $selectedColumn->_finalize($context));
            }

            $ret->sql = substr($ret->sql, 2);
            return $ret;
        } finally {
            $context->modelFinalizer = null;
        }
    }
}