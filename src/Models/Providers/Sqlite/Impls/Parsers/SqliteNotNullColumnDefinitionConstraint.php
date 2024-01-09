<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

use Magpie\General\Packs\PackContext;

/**
 * Column definition constraint for SQLite: 'NOT NULL'
 * @internal
 */
class SqliteNotNullColumnDefinitionConstraint extends SqliteColumnDefinitionConstraint
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'not-null';

    /**
     * @var SqliteColumnDefinitionConflictClause|null Associated conflict clause
     */
    public readonly ?SqliteColumnDefinitionConflictClause $conflict;


    /**
     * Constructor
     * @param string|null $name
     * @param SqliteColumnDefinitionConflictClause|null $conflict
     */
    protected function __construct(?string $name, ?SqliteColumnDefinitionConflictClause $conflict)
    {
        parent::__construct($name);

        $this->conflict = $conflict;
    }


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
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->conflict = $this->conflict;
    }


    /**
     * @inheritDoc
     */
    protected static function onParseSpecific(?string $name, SqliteTokenStream $tokens) : ?static
    {
        if (!$tokens->ifOptionalKeyword('NOT')) return null;

        $tokens->expectKeyword('NULL');
        $conflict = SqliteColumnDefinitionConflictClause::tryParse($tokens);

        return new static($name, $conflict);
    }
}