<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

use Magpie\Models\Providers\Sqlite\Exceptions\SqliteCannotParseTypeParserException;
use Magpie\Models\Providers\Sqlite\Exceptions\SqliteParserException;
use Magpie\Models\Providers\Sqlite\Impls\Concepts\TokenParseable;
use Magpie\Objects\CommonObject;

/**
 * Parsed SQLite items
 * @internal
 */
abstract class SqliteParsed extends CommonObject implements TokenParseable
{
    /**
     * Constructor
     */
    protected function __construct()
    {

    }


    /**
     * @inheritDoc
     */
    public static final function parse(SqliteTokenStream $tokens) : static
    {
        return static::onParse($tokens) ?? throw new SqliteCannotParseTypeParserException(static::class);
    }


    /**
     * @inheritDoc
     */
    public static final function tryParse(SqliteTokenStream $tokens) : ?static
    {
        return static::onParse($tokens);
    }


    /**
     * Handle parsing from given token stream
     * @param SqliteTokenStream $tokens
     * @return static|null
     * @throws SqliteParserException
     */
    protected static abstract function onParse(SqliteTokenStream $tokens) : ?static;
}