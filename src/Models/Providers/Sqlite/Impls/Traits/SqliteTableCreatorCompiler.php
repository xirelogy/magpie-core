<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Traits;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Sugars\Quote;
use Magpie\Models\Concepts\QueryIdentifierQuotable;
use Magpie\Models\Providers\QueryGrammar;
use Magpie\Models\Providers\Sqlite\Impls\SqliteColumnDatabaseSpecifier;
use Magpie\Models\Providers\Sqlite\SqliteConnection;

/**
 * Compile and SQLite table creator SQL statement
 * @internal
 */
trait SqliteTableCreatorCompiler
{
    /**
     * Compile a create table SQL statement
     * @param QueryGrammar $grammar
     * @param SqliteConnection $connection
     * @param string $tableName
     * @param iterable<SqliteColumnDatabaseSpecifier> $columns
     * @return string
     * @throws SafetyCommonException
     */
    protected static function compileCreateTableSql(QueryGrammar $grammar, SqliteConnection $connection, string $tableName, iterable $columns) : string
    {
        $q = $grammar->getIdentifierQuote();
        $sql = 'CREATE TABLE ' . $q->quote($tableName);

        // Prepare declarations
        $primaryKeys = [];
        $declarations = [];
        foreach ($columns as $column) {
            if ($column->isPrimaryKey()) $primaryKeys[] = $column->getName();
            $declarations[] = $column->_compile($connection);
        }

        if (count($primaryKeys) > 1) {
            $outPrimaryKeys = iter_flatten(static::applyQuotes($q, $primaryKeys));
            $declarations[] = 'PRIMARY KEY ' . Quote::bracket(implode(', ', $outPrimaryKeys));
        }

        // Finalize and return
        $sql .= ' ' . Quote::bracket(implode(', ', $declarations));
        return $sql;
    }


    /**
     * Escape all names provided
     * @param QueryIdentifierQuotable $q
     * @param iterable<string> $names
     * @return iterable<string>
     */
    protected static function applyQuotes(QueryIdentifierQuotable $q, iterable $names) : iterable
    {
        foreach ($names as $name) {
            yield $q->quote($name);
        }
    }
}