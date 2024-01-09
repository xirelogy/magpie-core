<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

/**
 * Table constraint for SQLite: 'PRIMARY KEY'
 * @internal
 */
class SqlitePrimaryKeyTableConstraint extends SqliteIndexedColumnsTableConstraint
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'primary-key';


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
        if (!$tokens->ifOptionalKeyword('PRIMARY')) return null;

        $tokens->expectKeyword('KEY');

        $columns = static::parseIndexedColumns($tokens);
        $conflict = SqliteColumnDefinitionConflictClause::tryParse($tokens);

        return new static($name, $columns, $conflict);
    }
}