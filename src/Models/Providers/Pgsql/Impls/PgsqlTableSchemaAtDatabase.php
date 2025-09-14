<?php

namespace Magpie\Models\Providers\Pgsql\Impls;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelWriteException;
use Magpie\Models\Providers\Pgsql\PgsqlConnection;
use Magpie\Models\Schemas\ColumnSchemaAtDatabase;
use Magpie\Models\Schemas\TableSchemaAtDatabase;

/**
 * PostgreSQL table schema at database level
 * @internal
 */
class PgsqlTableSchemaAtDatabase extends TableSchemaAtDatabase
{
    /**
     * @var array Associated record
     */
    protected array $record;
    /**
     * @var array<string, PgsqlColumnSchemaAtDatabase>
     */
    protected array $columns = [];


    /**
     * Constructor
     * @param PgsqlConnection $connection
     * @param array $record
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public function __construct(PgsqlConnection $connection, array $record)
    {
        $this->record = $record;
        $tableName = $this->getName();
        $tableMeta = new PgsqlTableMetadataAtDatabase($connection, $tableName);

        $columnRecords = static::queryColumnRecords($connection, $tableName);
        foreach ($columnRecords as $columnRecord) {
            $column = new PgsqlColumnSchemaAtDatabase($connection, $tableMeta, $columnRecord);
            $columnName = PgsqlColumnSchemaAtDatabase::normalizeName($column->getName());
            $this->columns[$columnName] = $column;
        }
    }


    /**
     * @inheritDoc
     */
    public function getName() : string
    {
        return $this->record['table_name'];
    }


    /**
     * @inheritDoc
     */
    public function getColumns() : iterable
    {
        yield from $this->columns;
    }


    /**
     * @inheritDoc
     */
    public function getColumn(string $name) : ?ColumnSchemaAtDatabase
    {
        $columnName = PgsqlColumnSchemaAtDatabase::normalizeName($name);
        return $this->columns[$columnName] ?? null;
    }


    /**
     * Query all column records
     * @param PgsqlConnection $connection
     * @param string $tableName
     * @return iterable<array>
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     * @noinspection SqlNoDataSourceInspection
     * @noinspection SqlDialectInspection
     */
    protected static function queryColumnRecords(PgsqlConnection $connection, string $tableName) : iterable
    {
        $schema = $connection->getSchema();

        $sql = 'SELECT * FROM information_schema.columns WHERE "table_schema" = ? AND "table_name" = ? ORDER BY "ordinal_position"';

        $command = $connection->prepare($sql);
        $command->bind([
            $schema,
            $tableName,
        ]);

        yield from $command->query();
    }
}