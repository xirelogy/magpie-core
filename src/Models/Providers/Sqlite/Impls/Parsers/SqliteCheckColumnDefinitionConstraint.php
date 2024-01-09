<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

use Magpie\General\Packs\PackContext;

/**
 * Column definition constraint for SQLite: 'CHECK'
 * @internal
 */
class SqliteCheckColumnDefinitionConstraint extends SqliteColumnDefinitionConstraint
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'check';

    /**
     * @var SqliteExpression Associated expression
     */
    public readonly SqliteExpression $expr;


    /**
     * Constructor
     * @param string|null $name
     * @param SqliteExpression $expr
     */
    protected function __construct(?string $name, SqliteExpression $expr)
    {
        parent::__construct($name);

        $this->expr = $expr;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->expr = $this->expr;
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
        if (!$tokens->ifOptionalKeyword('CHECK')) return null;

        $expr = SqliteExpression::parse($tokens);

        return new static($name, $expr);
    }
}