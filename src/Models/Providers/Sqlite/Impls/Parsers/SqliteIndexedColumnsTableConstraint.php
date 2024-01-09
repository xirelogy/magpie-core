<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

use Magpie\General\Packs\PackContext;
use Magpie\Models\Providers\Sqlite\Exceptions\SqliteParserException;

/**
 * Table constraint for SQLite utilizing indexed columns
 * @internal
 */
abstract class SqliteIndexedColumnsTableConstraint extends SqliteTableConstraint
{
    /**
     * @var array<SqliteConstraintIndexedColumn> All participating columns
     */
    public readonly array $columns;
    /**
     * @var SqliteColumnDefinitionConflictClause|null Associated conflict clause
     */
    public readonly ?SqliteColumnDefinitionConflictClause $conflict;


    /**
     * Constructor
     * @param string|null $name
     * @param iterable<SqliteConstraintIndexedColumn> $columns
     * @param SqliteColumnDefinitionConflictClause|null $conflict
     */
    protected function __construct(?string $name, iterable $columns, ?SqliteColumnDefinitionConflictClause $conflict)
    {
        parent::__construct($name);

        $this->columns = iter_flatten($columns, false);
        $this->conflict = $conflict;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->columns = $this->columns;
        $ret->conflict = $this->conflict;
    }


    /**
     * Parse for list of indexed columns
     * @param SqliteTokenStream $tokens
     * @return iterable
     * @throws SqliteParserException
     */
    protected static final function parseIndexedColumns(SqliteTokenStream $tokens) : iterable
    {
        $tokens->expectToken('(');
        for (;;) {
            yield SqliteConstraintIndexedColumn::parse($tokens);
            if ($tokens->ifOptionalToken(')')) return;

            $tokens->expectToken(',');
        }
    }
}