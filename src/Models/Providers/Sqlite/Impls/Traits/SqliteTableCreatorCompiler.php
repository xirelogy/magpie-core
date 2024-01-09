<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Traits;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Sugars\Quote;
use Magpie\Models\Providers\Sqlite\Impls\SqliteColumnDatabaseSpecifier;
use Magpie\Models\Providers\Sqlite\Impls\SqliteGrammar;
use Magpie\Models\Providers\Sqlite\SqliteConnection;

/**
 * Compile and SQLite table creator SQL statement
 * @internal
 */
trait SqliteTableCreatorCompiler
{
    /**
     * Compile a create table SQL statement
     * @param SqliteConnection $connection
     * @param string $tableName
     * @param iterable<SqliteColumnDatabaseSpecifier> $columns
     * @return string
     * @throws SafetyCommonException
     * @noinspection SqlNoDataSourceInspection
     * @noinspection SqlDialectInspection
     */
    protected static function compileCreateTableSql(SqliteConnection $connection, string $tableName, iterable $columns) : string
    {
        $sql = 'CREATE TABLE ' . SqliteGrammar::escapeName($tableName);

        // Prepare declarations
        $primaryKeys = [];
        $declarations = [];
        foreach ($columns as $column) {
            if ($column->isPrimaryKey()) $primaryKeys[] = $column->getName();
            $declarations[] = $column->_compile($connection);
        }

        if (count($primaryKeys) > 1) {
            $outPrimaryKeys = iter_flatten(static::applyEscapeNames($primaryKeys));
            $declarations[] = 'PRIMARY KEY ' . Quote::bracket(implode(', ', $outPrimaryKeys));
        }

        // Finalize and return
        $sql .= ' ' . Quote::bracket(implode(', ', $declarations));
        return $sql;
    }


    /**
     * Escape all names provided
     * @param iterable<string> $names
     * @return iterable<string>
     */
    protected static function applyEscapeNames(iterable $names) : iterable
    {
        foreach ($names as $name) {
            yield SqliteGrammar::escapeName($name);
        }
    }
}