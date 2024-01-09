<?php

namespace Magpie\Models\Providers\Sqlite\Impls;

use Magpie\Exceptions\DuplicatedKeyException;
use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Providers\Sqlite\Exceptions\SqliteParserException;
use Magpie\Models\Providers\Sqlite\Impls\Parsers\SqliteCreateTableStatement;
use Magpie\Models\Providers\Sqlite\Impls\Parsers\SqliteTokenStream;
use Magpie\Models\Providers\Sqlite\SqliteConnection;
use Magpie\Models\Schemas\ColumnSchemaAtDatabase;
use Magpie\Models\Schemas\TableSchemaAtDatabase;

/**
 * SQLite table schema at database level
 * @internal
 */
class SqliteTableSchemaAtDatabase extends TableSchemaAtDatabase
{
    /**
     * @var SqliteConnection Related connection
     */
    protected readonly SqliteConnection $connection;
    /**
     * @var array Associated record
     */
    protected readonly array $record;
    /**
     * @var array<string, SqliteColumnSchemaAtDatabase> Associated columns
     */
    protected readonly array $columns;


    /**
     * Constructor
     * @param SqliteConnection $connection
     * @param array $tableRecord
     * @param iterable $columnRecords
     * @throws SafetyCommonException
     * @throws SqliteParserException
     */
    public function __construct(SqliteConnection $connection, array $tableRecord, iterable $columnRecords)
    {
        $this->connection = $connection;
        $this->record = $tableRecord;
        $this->columns = static::buildColumns($connection, $tableRecord, $columnRecords);
    }


    /**
     * @inheritDoc
     */
    public function getName() : string
    {
        return $this->record['name'];
    }


    /**
     * @inheritDoc
     */
    public function getColumns() : iterable
    {
        foreach ($this->columns as $column) {
            yield $column;
        }
    }


    /**
     * @inheritDoc
     */
    public function getColumn(string $name) : ?ColumnSchemaAtDatabase
    {
        $columnNameKey = strtolower($name);
        returN $this->columns[$columnNameKey] ?? null;
    }


    /**
     * Build columns from table record and column records
     * @param SqliteConnection $connection
     * @param array $tableRecord
     * @param iterable<array> $columnRecords
     * @return array<string, ColumnSchemaAtDatabase>
     * @throws SafetyCommonException
     * @throws SqliteParserException
     */
    private static function buildColumns(SqliteConnection $connection, array $tableRecord, iterable $columnRecords) : array
    {
        $tableName = $tableRecord['name'] ?? throw new InvalidDataException();
        $sql = $tableRecord['sql'] ?? throw new InvalidDataException();

        $tokens = SqliteTokenStream::from($sql);
        $statement = SqliteCreateTableStatement::parse($tokens);
        if ($statement->tableName !== $tableName) throw new InvalidDataException();

        // Collect all column hints
        $hints = [];
        $totalExpectedColumns = 0;

        foreach ($columnRecords as $columnRecord) {
            $columnName = $columnRecord['name'] ?? null;
            if ($columnName === null) continue;

            $hints[strtolower($columnName)] = $columnRecord;
            ++$totalExpectedColumns;
        }

        // Process the sql statement
        $retColumns = [];
        $totalProcessedColumns = 0;
        foreach ($statement->columns as $column) {
            $columnName = $column->columnName;
            $columnNameKey = strtolower($columnName);
            if (!array_key_exists($columnNameKey, $hints)) continue;
            if (array_key_exists($columnNameKey, $retColumns)) throw new DuplicatedKeyException($columnName);
            $retColumns[$columnNameKey] = new SqliteColumnSchemaAtDatabase($connection, $column, $hints[$columnNameKey]);
            ++$totalProcessedColumns;
        }

        return $retColumns;
    }
}