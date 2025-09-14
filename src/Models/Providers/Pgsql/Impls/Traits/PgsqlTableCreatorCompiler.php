<?php

namespace Magpie\Models\Providers\Pgsql\Impls\Traits;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Str;
use Magpie\General\Sugars\Quote;
use Magpie\Models\Concepts\QueryIdentifierQuotable;
use Magpie\Models\Providers\Pgsql\Impls\PgsqlColumnDatabaseSpecifier;
use Magpie\Models\Providers\Pgsql\PgsqlConnection;
use Magpie\Models\Providers\QueryGrammar;

/**
 * Compile PostgreSQL table creator SQL statement
 * @internal
 */
trait PgsqlTableCreatorCompiler
{
    /**
     * Compile a 'create table' SQL statement
     * @param QueryGrammar $grammar
     * @param PgsqlConnection $connection
     * @param string $tableName
     * @param iterable<PgsqlColumnDatabaseSpecifier> $columns
     * @return iterable<string>
     * @throws SafetyCommonException
     * @noinspection SqlNoDataSourceInspection
     */
    protected static function compileCreateTableSql(QueryGrammar $grammar, PgsqlConnection $connection, string $tableName, iterable $columns) : iterable
    {
        $schema = $connection->getSchema();
        $q = $grammar->getIdentifierQuote();

        // Initial 'CREATE TABLE' statement
        $sql = 'CREATE TABLE ' . $q->quote($schema) . '.' . $q->quote($tableName);

        // Setup declarations
        $primaryKeys = [];
        $comments = [];
        $declarations = [];
        foreach ($columns as $column) {
            $declarations[] = $column->_compile($connection);
            if ($column->isPrimaryKey()) {
                $primaryKeys[] = $column->getName();
            }

            $columnComments = $column->getComments();
            if (!Str::isNullOrEmpty($columnComments)) {
                $comments[$column->getName()] = $columnComments;
            }
        }

        // Handle primary keys
        if (count($primaryKeys) > 0) {
            $outPrimaryKeys = iter_flatten(static::applyQuotes($q, $primaryKeys));
            $declarations[] = 'PRIMARY KEY ' . Quote::bracket(implode(', ', $outPrimaryKeys));
        }

        // Finalize
        $sql .= ' ' . Quote::bracket(implode(', ', $declarations));
        yield $sql;

        // Comments SQL
        foreach ($comments as $columnName => $columnComments) {
            $commentSql = 'COMMENT ON COLUMN '
                . $q->quote($schema) . '.' . $q->quote($tableName) . '.' . $columnName
                . ' IS ' . $connection->quoteString($columnComments);
            yield $commentSql;
        }
    }


    /**
     * Apply identifier quotes
     * @param QueryIdentifierQuotable $q
     * @param iterable<string> $values
     * @return iterable<string>
     */
    protected static function applyQuotes(QueryIdentifierQuotable $q, iterable $values) : iterable
    {
        foreach ($values as $value) {
            yield $q->quote($value);
        }
    }
}