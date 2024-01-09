<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

use Magpie\General\Packs\PackContext;

/**
 * Column definition constraint for SQLite: 'PRIMARY KEY'
 * @internal
 */
class SqlitePrimaryKeyColumnDefinitionConstraint extends SqliteColumnDefinitionConstraint
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'primary-key';

    /**
     * @var string|null Specific order
     */
    public readonly ?string $order;
    /**
     * @var SqliteColumnDefinitionConflictClause|null Associated conflict clause
     */
    public readonly ?SqliteColumnDefinitionConflictClause $conflict;
    /**
     * @var bool If 'auto-increment' specified
     */
    public readonly bool $isAutoIncrement;


    /**
     * Constructor
     * @param string|null $name
     * @param string|null $order
     * @param SqliteColumnDefinitionConflictClause|null $conflict
     * @param bool $isAutoIncrement
     */
    protected function __construct(?string $name, ?string $order, ?SqliteColumnDefinitionConflictClause $conflict, bool $isAutoIncrement)
    {
        parent::__construct($name);

        $this->order = $order;
        $this->conflict = $conflict;
        $this->isAutoIncrement = $isAutoIncrement;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->order = $this->order;
        $ret->conflict = $this->conflict;
        $ret->isAutoIncrement = $this->isAutoIncrement;
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
        if (!$tokens->ifOptionalKeyword('PRIMARY')) return null;

        $tokens->expectKeyword('KEY');
        $order = $tokens->optionalAnyKeyword('ASC', 'DESC');
        $conflict = SqliteColumnDefinitionConflictClause::tryParse($tokens);
        $isAutoIncrement = $tokens->ifOptionalKeyword('AUTOINCREMENT');

        return new static($name, $order, $conflict, $isAutoIncrement);
    }
}