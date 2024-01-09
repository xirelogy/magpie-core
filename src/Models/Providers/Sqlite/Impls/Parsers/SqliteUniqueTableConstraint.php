<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

/**
 * Table constraint for SQLite: 'UNIQUE'
 * @internal
 */
class SqliteUniqueTableConstraint extends SqliteIndexedColumnsTableConstraint
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'unique';


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    protected static function onParseSpecific(?string $name, SqliteTokenStream $tokens) : ?static
    {
        if (!$tokens->ifOptionalKeyword('UNIQUE')) return null;

        $columns = static::parseIndexedColumns($tokens);
        $conflict = SqliteColumnDefinitionConflictClause::tryParse($tokens);

        return new static($name, $columns, $conflict);
    }
}