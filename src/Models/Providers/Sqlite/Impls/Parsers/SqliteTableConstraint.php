<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

use Magpie\General\Concepts\TypeClassable;

/**
 * Table constraint for SQLite
 * @internal
 */
abstract class SqliteTableConstraint extends SqliteConstraint implements TypeClassable
{
    /**
     * @inheritDoc
     */
    protected static final function getSpecificClasses() : iterable
    {
        yield SqlitePrimaryKeyTableConstraint::class;
        yield SqliteUniqueTableConstraint::class;
    }
}