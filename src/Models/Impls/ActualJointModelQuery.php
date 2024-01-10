<?php

namespace Magpie\Models\Impls;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Models\ColumnExpressionSelect;
use Magpie\Models\ColumnName;
use Magpie\Models\Concepts\ConnectionResolvable;
use Magpie\Models\Concepts\JointSpecifiable;
use Magpie\Models\Connection;
use Magpie\Models\Impls\Traits\CommonActualModelQuery;
use Magpie\Models\Impls\Traits\WithQueryFilterService;
use Magpie\Models\Query;
use Magpie\Models\Schemas\TableSchema;
use Magpie\Models\Statement;

/**
 * Query implementation for joint-model
 * @internal
 */
class ActualJointModelQuery extends Query
{
    use WithQueryFilterService;
    use CommonActualModelQuery;


    /**
     * @var ConnectionResolvable|string Model connection
     */
    protected ConnectionResolvable|string $connection;
    /**
     * @var array<string, TableSchema> All table schemas
     */
    protected array $tableSchemas;
    /**
     * @var array<JointSpecifiable> Joint specifications
     */
    protected array $jointSpecs;


    /**
     * Constructor
     * @param ConnectionResolvable|string $connection
     * @param array<string, TableSchema> $tableSchemas
     * @param array<ActualJointSpecification> $jointSpecs
     */
    public function __construct(ConnectionResolvable|string $connection, array $tableSchemas, array $jointSpecs)
    {
        parent::__construct(null);

        $this->connection = $connection;
        $this->tableSchemas = $tableSchemas;
        $this->jointSpecs = $jointSpecs;
    }


    /**
     * @inheritDoc
     */
    protected function prepareSelectStatement(FilterApplyMode $filterMode, ?ModelFinalizer &$modelFinalizer = null) : Statement
    {
        $connection = Connection::from($this->connection);
        $context = new QueryContext($connection, TableSchema::from(TemporaryModel::class));

        $selectionFinalized = $this->finalizeSelects($context, $modelFinalizer);
        $fromFinalized = $this->finalizeJoints($context);

        $query = new QueryStatement('SELECT ' . $selectionFinalized->sql . ' FROM ' . $fromFinalized->sql, $selectionFinalized->values);

        return $this->commonPrepareSelectStatement($query, $context, $connection, $filterMode);
    }


    /**
     * @inheritDoc
     */
    protected function duplicateQuery(bool $isClearFilter) : static
    {
        $ret = new static($this->connection, $this->tableSchemas, $this->jointSpecs);
        $ret->duplicateFrom($this, $isClearFilter);
        return $ret;
    }


    /**
     * Finalize selections
     * @param QueryContext $context
     * @param ModelFinalizer|null $modelFinalizer
     * @return QueryStatement
     * @throws SafetyCommonException
     */
    private function finalizeSelects(QueryContext $context, ?ModelFinalizer &$modelFinalizer = null) : QueryStatement
    {
        $ret = new QueryStatement('');

        if (count($this->selectedColumns) <= 0) {
            $modelFinalizer = new ActualJointModelFinalizer();
            $columnIndex = 0;

            foreach ($this->tableSchemas as $tableSchema) {
                foreach ($tableSchema->getColumns() as $column) {
                    ++$columnIndex;
                    $columnName = $column->getName();
                    $aliasName = 'jc_' . $columnIndex;
                    $fullColumnName = ColumnName::fromTable($tableSchema, $columnName);

                    $aliased = new ColumnExpressionSelect($fullColumnName, $aliasName);
                    $ret->appendJoinIfNotEmpty(', ', $aliased->_finalize($context));

                    $modelFinalizer->declareSelectAlias($tableSchema, $columnName, $aliasName);
                }
            }
        } else {
            foreach ($this->selectedColumns as $selectedColumn) {
                $ret->appendJoinIfNotEmpty(', ', $selectedColumn->_finalize($context));
            }
        }

        $ret->sql = substr($ret->sql, 2);
        return $ret;
    }


    /**
     * Finalize joints
     * @param QueryContext $context
     * @return QueryStatement
     * @throws SafetyCommonException
     */
    private function finalizeJoints(QueryContext $context) : QueryStatement
    {
        /** @var TableSchema $baseTableSchema */
        $baseTableSchema = iter_first($this->tableSchemas);

        $ret = new QueryStatement(SqlFormat::backTick($baseTableSchema->getName()));
        foreach ($this->jointSpecs as $jointSpec) {
            $ret->appendJoinIfNotEmpty(' ', $jointSpec->_finalize($context));
        }

        return $ret;
    }


    /**
     * @inheritDoc
     */
    public function _subFinalize(QueryContext $context) : QueryStatement
    {
        throw new UnsupportedException();
    }
}