<?php

namespace Magpie\Models\Providers\Mysql\Impls;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelWriteException;
use Magpie\Models\Providers\Mysql\MysqlConnection;
use Magpie\Models\Schemas\ColumnSchemaAtDatabase;
use Magpie\Models\Schemas\TableSchemaAtDatabase;

/**
 * MySQL table schema at database level
 * @internal
 */
class MysqlTableSchemaAtDatabase extends TableSchemaAtDatabase
{
    /**
     * @var array Associated record
     */
    protected array $record;
    /**
     * @var array<string, MysqlColumnSchemaAtDatabase>
     */
    protected array $columns = [];


    /**
     * Constructor
     * @param MysqlConnection $connection
     * @param array $record
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public function __construct(MysqlConnection $connection, array $record)
    {
        $this->record = $record;

        $columnRecords = static::queryColumnRecords($connection, $this->getName());
        foreach ($columnRecords as $columnRecord) {
            $column = new MysqlColumnSchemaAtDatabase($connection, $columnRecord);
            $columnName = MysqlColumnSchemaAtDatabase::normalizeName($column->getName());
            $this->columns[$columnName] = $column;
        }
    }


    /**
     * @inheritDoc
     */
    public function getName() : string
    {
        return $this->record['TABLE_NAME'];
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
        $columnName = MysqlColumnSchemaAtDatabase::normalizeName($name);
        return $this->columns[$columnName] ?? null;
    }


    /**
     * Query all column records
     * @param MysqlConnection $connection
     * @param string $tableName
     * @return iterable<array>
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     * @noinspection SqlNoDataSourceInspection
     * @noinspection SqlDialectInspection
     */
    protected static function queryColumnRecords(MysqlConnection $connection, string $tableName) : iterable
    {
        $database = $connection->getDatabase();
        if ($database === null) return;

        $sql = 'SELECT * FROM information_schema.columns WHERE `table_schema` = ? AND `table_name` = ? ORDER BY ordinal_position';

        $command = $connection->prepare($sql);
        $command->bind([
            $database,
            $tableName,
        ]);

        yield from $command->query();
    }
}