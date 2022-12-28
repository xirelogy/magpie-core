<?php

namespace Magpie\Models\Impls;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\ColumnName;
use Magpie\Models\Connection;
use Magpie\Models\Providers\QueryGrammar;
use Magpie\Models\Schemas\ColumnSchema;
use Magpie\Models\Schemas\TableSchema;

/**
 * Context to build query
 * @internal
 */
class QueryContext
{
    /**
     * @var Connection|null Associated connection
     */
    public readonly ?Connection $connection;
    /**
     * @var QueryGrammar|null Associated query grammar
     */
    public readonly ?QueryGrammar $grammar;
    /**
     * @var TableSchema|null Associated table schema
     */
    public readonly ?TableSchema $tableSchema;
    /**
     * @var ModelFinalizer|null Associated model finalizer
     */
    public ?ModelFinalizer $modelFinalizer = null;


    /**
     * Constructor
     * @param Connection|null $connection
     * @param TableSchema|null $tableSchema
     */
    public function __construct(?Connection $connection, ?TableSchema $tableSchema)
    {
        $this->connection = $connection;
        $this->grammar = $connection?->getQueryGrammar();
        $this->tableSchema = $tableSchema;
    }


    /**
     * Express column name in SQL
     * @param string|ColumnName $columnName
     * @return string
     */
    public function getColumnNameSql(string|ColumnName $columnName) : string
    {
        if (is_string($columnName)) {
            return SqlFormat::backTick($columnName);
        }

        if ($columnName instanceof ColumnName) {
            return $columnName->toSql($this->tableSchema);
        }

        return SqlFormat::backTick('');    // Should not reach here!
    }


    /**
     * Get corresponding column schema
     * @param string|ColumnName $columnName
     * @return ColumnSchema|null
     * @throws SafetyCommonException
     */
    public function getColumnSchema(string|ColumnName $columnName) : ?ColumnSchema
    {
        if (is_string($columnName)) {
            return $this->tableSchema?->getColumn($columnName) ?? null;
        }

        if ($columnName instanceof ColumnName) {
            return $columnName->getSchema($this->tableSchema);
        }

        return null;    // Should not reach here!
    }
}