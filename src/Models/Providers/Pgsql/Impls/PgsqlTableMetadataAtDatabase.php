<?php

namespace Magpie\Models\Providers\Pgsql\Impls;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelWriteException;
use Magpie\Models\Providers\Pgsql\PgsqlConnection;

/**
 * PostgreSQL table metadata at database level
 * @internal
 */
class PgsqlTableMetadataAtDatabase
{
    /**
     * @var array<string, string> Primary key columns
     */
    protected array $primaryKeyColumns = [];
    /**
     * @var array<string, string> Unique columns
     */
    protected array $uniqueColumns = [];


    /**
     * Constructor
     * @param PgsqlConnection $connection
     * @param string $tableName
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public function __construct(PgsqlConnection $connection, string $tableName)
    {
        foreach (static::queryColumnPrimaryKeys($connection, $tableName) as $record) {
            $constraintName = $record['kcu_constraint_name'];
            $columnName = $record['kcu_column_name'];

            $columnKey = PgsqlColumnSchemaAtDatabase::normalizeName($columnName);

            switch ($constraintName) {
                case 'PRIMARY KEY':
                    $this->primaryKeyColumns[$columnKey] = $columnName;
                    break;
                case 'UNIQUE':
                    $this->uniqueColumns[$columnKey] = $columnName;
                    break;
            }
        }
    }


    /**
     * If given column is primary key
     * @param string $columnName
     * @return bool
     */
    public function isPrimaryKey(string $columnName) : bool
    {
        $columnKey = PgsqlColumnSchemaAtDatabase::normalizeName($columnName);
        return array_key_exists($columnKey, $this->primaryKeyColumns);
    }


    /**
     * If given column is unique
     * @param string $columnName
     * @return bool
     */
    public function isUnique(string $columnName) : bool
    {
        $columnKey = PgsqlColumnSchemaAtDatabase::normalizeName($columnName);
        return array_key_exists($columnKey, $this->uniqueColumns);
    }


    /**
     * Query all primary key columns
     * @param PgsqlConnection $connection
     * @param string $tableName
     * @return iterable<array{kcu_constraint_name: string, kcu_column_name: string}>
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     * @noinspection SqlNoDataSourceInspection
     * @noinspection SqlDialectInspection
     */
    protected static function queryColumnPrimaryKeys(PgsqlConnection $connection, string $tableName) : iterable
    {
        $schema = $connection->getSchema();

        $sql = <<<EOT
            SELECT 
                "kcu"."constraint_name" AS "kcu_constraint_name",
                "kcu"."column_name" AS "kcu_column_name"
            FROM "information_schema"."table_constraints" AS "tc"
                JOIN "information_schema"."key_column_usage" AS "kcu"
                ON "tc"."constraint_name" = "kcu"."constraint_name"
                AND "tc"."table_schema" = "kcu"."table_schema"
            WHERE "tc"."constraint_type" IN (?, ?)
                AND "tc"."table_schema" = ?
                AND "tc"."table_name" = ?;
        EOT;

        $command = $connection->prepare($sql);
        $command->bind([
            'PRIMARY KEY',
            'UNIQUE',
            $schema,
            $tableName,
        ]);

        yield from $command->query();
    }
}