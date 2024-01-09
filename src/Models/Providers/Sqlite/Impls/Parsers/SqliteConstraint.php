<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

use Magpie\Models\Providers\Sqlite\Exceptions\SqliteCannotParseTypeParserException;
use Magpie\Models\Providers\Sqlite\Exceptions\SqliteParserException;

/**
 * Constraint for SQLite
 * @internal
 */
abstract class SqliteConstraint extends SqliteParsed
{
    /**
     * @var string|null Constraint name
     */
    public readonly ?string $name;


    /**
     * Constructor
     * @param string|null $name
     */
    protected function __construct(?string $name)
    {
        parent::__construct();

        $this->name = $name;
    }


    /**
     * @inheritDoc
     */
    protected static final function onParse(SqliteTokenStream $tokens) : ?static
    {
        $name = static::parseConstraintName($tokens);

        /** @var class-string<self> $class */
        foreach (static::getSpecificClasses() as $class) {
            $parsed = $class::onParseSpecific($name, $tokens);
            if ($parsed !== null) return $parsed;
        }

        // If constraint name is given, then this must be successful
        if ($name !== null) throw new SqliteCannotParseTypeParserException(static::class);

        return null;
    }


    /**
     * Handle specific parsing from given token stream
     * @param string|null $name
     * @param SqliteTokenStream $tokens
     * @return static|null
     * @throws SqliteParserException
     */
    protected static abstract function onParseSpecific(?string $name, SqliteTokenStream $tokens) : ?static;


    /**
     * All specific types
     * @return iterable<class-string<self>>
     */
    protected static abstract function getSpecificClasses() : iterable;


    /**
     * Parse for optional constraint name
     * @param SqliteTokenStream $tokens
     * @return string|null
     * @throws SqliteParserException
     */
    protected static final function parseConstraintName(SqliteTokenStream $tokens) : ?string
    {
        if (!$tokens->ifOptionalKeyword('CONSTRAINT')) return null;

        return $tokens->expectName();
    }
}