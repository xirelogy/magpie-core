<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Concepts;

use Magpie\Models\Providers\Sqlite\Exceptions\SqliteParserException;
use Magpie\Models\Providers\Sqlite\Impls\Parsers\SqliteTokenStream;

/**
 * May parse from tokens
 * @internal
 */
interface TokenParseable
{
    /**
     * Parse from given token stream
     * @param SqliteTokenStream $tokens
     * @return static
     * @throws SqliteParserException
     */
    public static function parse(SqliteTokenStream $tokens) : static;


    /**
     * Try to parse from given token stream
     * @param SqliteTokenStream $tokens
     * @return static|null
     * @throws SqliteParserException
     */
    public static function tryParse(SqliteTokenStream $tokens) : ?static;
}