<?php

namespace Magpie\Models\Providers\Pgsql\Impls;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Sugars\Quote;
use Magpie\Models\Concepts\ColumnDatabaseEditSpecifiable;
use Magpie\Models\Providers\Pgsql\Impls\Traits\PgsqlTableCreatorCompiler;
use Magpie\Models\Providers\Pgsql\PgsqlConnection;
use Magpie\Models\Providers\QueryGrammar;
use Magpie\Models\Schemas\ColumnSchema;
use Magpie\Models\Schemas\DatabaseEdits\AddColumnDatabaseEditAction;
use Magpie\Models\Schemas\DatabaseEdits\TableEditor;

/**
 * PostgreSQL table editor
 * @internal
 */
class PgsqlTableEditor extends TableEditor
{
    use PgsqlTableCreatorCompiler;

    /**
     * @var PgsqlConnection Associated connection
     */
    protected PgsqlConnection $connection;
    /**
     * @var array<PgsqlColumnDatabaseSpecifier> Target schema
     */
    protected readonly array $targetColumns;
    /**
     * @var array<string, PgsqlColumnDatabaseEditSpecifier> Column declarations
     */
    protected array $columns = [];


    /**
     * Constructor
     * @param PgsqlConnection $connection
     * @param string $tableName
     * @param iterable<ColumnSchema> $targetColumns
     */
    public function __construct(PgsqlConnection $connection, string $tableName, iterable $targetColumns)
    {
        parent::__construct($tableName);

        $this->connection = $connection;
        $this->targetColumns = static::acceptTargetColumns($targetColumns);
    }


    /**
     * @inheritDoc
     */
    public function hasColumn() : bool
    {
        return count($this->columns) > 0;
    }


    /**
     * @inheritDoc
     */
    public function addColumn(string $name) : ColumnDatabaseEditSpecifiable
    {
        $column = new PgsqlColumnDatabaseEditSpecifier($name);
        $columnKey = strtolower($name);
        $this->columns[$columnKey] = $column;

        return $column;
    }


    /**
     * @inheritDoc
     */
    public function isUseTransaction() : bool
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    public function compile(QueryGrammar $grammar) : iterable
    {
        foreach ($this->onCompile($grammar) as $sql) {
            yield $this->connection->prepare($sql);
        }
    }


    /**
     * Compile into SQL statements (string)
     * @return iterable<string>
     * @throws SafetyCommonException
     * @noinspection SqlNoDataSourceInspection
     * @noinspection SqlDialectInspection
     */
    protected function onCompile(QueryGrammar $grammar) : iterable
    {
        $schema = $this->connection->getSchema();
        $q = $grammar->getIdentifierQuote();

        // Find out what columns are preserved and exist in the new table
        $escapedExistingColumns = [];
        foreach ($this->targetColumns as $column) {
            $columnKey = strtolower($column->getName());

            // Ignore newly added columns
            if (array_key_exists($columnKey, $this->columns)) {
                $newColumn = $this->columns[$columnKey];
                if ($newColumn->getEditAction() instanceof AddColumnDatabaseEditAction) continue;
            }

            $escapedExistingColumns[] = $q->quote($column->getName());
        }
        $existingColumns = implode(', ', $escapedExistingColumns);

        // Prepare variables
        $tempTableName = '__magpie_temp';

        // Create new table, copy data, drop old table, and rename new table
        yield from static::compileCreateTableSql($grammar, $this->connection, $tempTableName, $this->targetColumns);
        yield 'INSERT INTO ' . $q->quote($schema) . '.' . $q->quote($tempTableName) . ' ' . Quote::bracket($existingColumns) . ' SELECT ' . $existingColumns . ' FROM ' . $q->quote($schema) . '.' . $q->quote($this->tableName);
        yield 'DROP TABLE ' . $q->quote($schema) . '.' . $q->quote($this->tableName);
        yield 'ALTER TABLE ' . $q->quote($schema) . '.' . $q->quote($tempTableName) . ' RENAME TO ' . $q->quote($this->tableName);
    }


    /**
     * @inheritDoc
     */
    protected static function isColumnDefinitionTypeCompatible(string $columnType, string $columnTypeAtDb) : bool
    {
        $columnType = static::translateColumnType($columnType);
        $columnTypeAtDb = static::translateColumnTypeAtDb($columnTypeAtDb);

        if ($columnType === $columnTypeAtDb) return true;

        // Booleans are not adaptable
        if ($columnType === 'bool' && $columnTypeAtDb !== 'bool') return false;

        return parent::isColumnDefinitionTypeCompatible($columnType, $columnTypeAtDb);
    }


    /**
     * Translate column type
     * @param string $columnType
     * @return string
     */
    private static function translateColumnType(string $columnType) : string
    {
        $columnType = strtolower($columnType);

        return match ($columnType) {
            'utinyint',
            'tinyint',
            'usmallint',
                => 'smallint',
            'uint',
                => 'int',
            'ubigint',
                => 'bigint',
            'float4',
                => 'float',
            'float8',
                => 'double',
            default,
                => $columnType,
        };
    }


    /**
     * Translate database column type
     * @param string $columnTypeAtDb
     * @return string
     */
    private static function translateColumnTypeAtDb(string $columnTypeAtDb) : string
    {
        $columnTypeAtDb = strtolower($columnTypeAtDb);

        return match ($columnTypeAtDb) {
            'int2',
                => 'smallint',
            'int4',
            'integer',
                => 'int',
            'int8',
                => 'bigint',
            'timestamptz',
                => 'timestamp',
            default,
                => $columnTypeAtDb,
        };

    }


    /**
     * Accept all target columns
     * @param iterable<ColumnSchema> $columns
     * @return array<ColumnDatabaseEditSpecifiable>
     */
    protected static function acceptTargetColumns(iterable $columns) : array
    {
        $ret = [];

        foreach ($columns as $column) {
            $columnName = $column->getName();
            $retColumn = new PgsqlColumnDatabaseSpecifier($columnName);
            static::mergeColumnFromSchema($retColumn, $column);

            $ret[] = $retColumn;
        }

        return $ret;
    }
}