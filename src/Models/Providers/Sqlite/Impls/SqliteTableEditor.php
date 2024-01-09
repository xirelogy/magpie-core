<?php

namespace Magpie\Models\Providers\Sqlite\Impls;

use Exception;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Sugars\Quote;
use Magpie\Models\Concepts\ColumnDatabaseEditSpecifiable;
use Magpie\Models\Providers\Sqlite\Impls\Traits\SqliteTableCreatorCompiler;
use Magpie\Models\Providers\Sqlite\SqliteConnection;
use Magpie\Models\Schemas\ColumnSchema;
use Magpie\Models\Schemas\DatabaseEdits\AddColumnDatabaseEditAction;
use Magpie\Models\Schemas\DatabaseEdits\TableEditor;
use Magpie\Models\Schemas\ModelDefinition;

/**
 * SQLite table editor
 * @internal
 */
class SqliteTableEditor extends TableEditor
{
    use SqliteTableCreatorCompiler;

    /**
     * @var SqliteConnection Associated connection
     */
    protected SqliteConnection $connection;
    /**
     * @var array<SqliteColumnDatabaseSpecifier> Target schema
     */
    protected readonly array $targetColumns;
    /**
     * @var array<string, SqliteColumnDatabaseEditSpecifier> Column declarations
     */
    protected array $columns = [];


    /**
     * Constructor
     * @param SqliteConnection $connection
     * @param string $tableName
     * @param iterable<ColumnSchema> $targetColumns
     */
    public function __construct(SqliteConnection $connection, string $tableName, iterable $targetColumns)
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
        $column = new SqliteColumnDatabaseEditSpecifier($name);
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
    public function compile() : iterable
    {
        foreach ($this->onCompile() as $sql) {
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
    protected function onCompile() : iterable
    {
        // Find out what columns are preserved and exist in the new table
        $escapedExistingColumns = [];
        foreach ($this->targetColumns as $column) {
            $columnKey = strtolower($column->getName());

            // Ignore newly added columns
            if (array_key_exists($columnKey, $this->columns)) {
                $newColumn = $this->columns[$columnKey];
                if ($newColumn->getEditAction() instanceof AddColumnDatabaseEditAction) continue;
            }

            $escapedExistingColumns[] = SqliteGrammar::escapeName($column->getName());
        }
        $existingColumns = implode(', ', $escapedExistingColumns);

        // Prepare variables
        $tempTableName = '@magpie-temp';

        // Rename, delete, recreate, import
        yield 'PRAGMA foreign_keys = 0';
        yield 'CREATE TABLE ' . SqliteGrammar::escapeName($tempTableName) . ' AS SELECT * FROM ' . SqliteGrammar::escapeName($this->tableName);
        yield 'DROP TABLE ' . SqliteGrammar::escapeName($this->tableName);
        yield static::compileCreateTableSql($this->connection, $this->tableName, $this->targetColumns);
        yield 'INSERT INTO ' . SqliteGrammar::escapeName($this->tableName). ' ' . Quote::bracket($existingColumns) . ' SELECT ' . $existingColumns . ' FROM ' . SqliteGrammar::escapeName($tempTableName);
        yield 'DROP TABLE ' . SqliteGrammar::escapeName($tempTableName);
        yield 'PRAGMA foreign_keys = 1';
    }


    /**
     * @inheritDoc
     */
    protected static function isColumnDefinitionTypeCompatible(string $columnType, string $columnTypeAtDb) : bool
    {
        try {
            $columnTypeDef = ModelDefinition::parse(strtolower($columnType));
            $columnTypeAtDbDef = ModelDefinition::parse(strtolower($columnTypeAtDb));

            // int and integer is compatible
            if ($columnTypeDef->baseType === 'int' && $columnTypeAtDbDef->baseType === 'integer') return true;
        } catch (Exception) {
            return false;
        }

        return parent::isColumnDefinitionTypeCompatible($columnType, $columnTypeAtDb);
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
            $retColumn = new SqliteColumnDatabaseSpecifier($columnName);
            static::mergeColumnFromSchema($retColumn, $column);

            $ret[] = $retColumn;
        }

        return $ret;
    }
}