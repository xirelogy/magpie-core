<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

use Magpie\General\Concepts\TypeClassable;
use Magpie\General\Packs\PackContext;
use Magpie\Models\Providers\Sqlite\Exceptions\SqliteParserException;

/**
 * An expression for SQLite
 * @internal
 */
abstract class SqliteExpression extends SqliteParsed implements TypeClassable
{
    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->typeClass = static::getTypeClass();
    }


    /**
     * @inheritDoc
     */
    protected static final function onParse(SqliteTokenStream $tokens) : ?static
    {
        // TODO: currently always use dummy expression
        return SqliteDummyExpression::onParseSpecific($tokens);
    }


    /**
     * Handle specific parsing from given token stream
     * @param SqliteTokenStream $tokens
     * @return static|null
     * @throws SqliteParserException
     */
    protected static abstract function onParseSpecific(SqliteTokenStream $tokens) : ?static;
}