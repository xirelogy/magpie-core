<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

use Magpie\General\Packs\PackContext;

/**
 * Column definition constraint for SQLite: 'UNIQUE'
 * @internal
 */
class SqliteUniqueColumnDefinitionConstraint extends SqliteColumnDefinitionConstraint
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'unique';

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
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->conflict = $this->conflict;
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
    protected static function onParseSpecific(?string $name, SqliteTokenStream $tokens) : ?static
    {
        if (!$tokens->ifOptionalKeyword('UNIQUE')) return null;

        $conflict = SqliteColumnDefinitionConflictClause::tryParse($tokens);

        return new static($name, $conflict);
    }
}