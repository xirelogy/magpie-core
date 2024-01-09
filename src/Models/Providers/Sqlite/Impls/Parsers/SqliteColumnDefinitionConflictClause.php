<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

use Magpie\General\Packs\PackContext;

/**
 * Conflict clause in SQLite column definition
 */
class SqliteColumnDefinitionConflictClause extends SqliteParsed
{
    /**
     * @var string Conflict action
     */
    public readonly string $action;


    /**
     * Constructor
     * @param string $action
     */
    protected function __construct(string $action)
    {
        parent::__construct();

        $this->action = $action;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->action = $this->action;
    }


    /**
     * @inheritDoc
     */
    protected static function onParse(SqliteTokenStream $tokens) : ?static
    {
        if (!$tokens->ifOptionalKeyword('ON')) return null;

        $tokens->expectKeyword('CONFLICT');

        $action = $tokens->expectAnyKeyword(
            'ROLLBACK',
            'ABORT',
            'FAIL',
            'IGNORE',
            'REPLACE',
        );

        return new static($action);
    }
}