<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

use Magpie\General\Packs\PackContext;

/**
 * Indexed-column in SQLite constraint
 * @internal
 */
class SqliteConstraintIndexedColumn extends SqliteParsed
{
    /**
     * @var string Column name
     */
    public readonly string $columnName;
    /**
     * @var string|null Collation name, if any
     */
    public readonly ?string $collateName;
    /**
     * @var string|null Order, if any
     */
    public readonly ?string $order;


    /**
     * Constructor
     * @param string $columnName
     * @param string|null $collateName
     * @param string|null $order
     */
    protected function __construct(string $columnName, ?string $collateName, ?string $order)
    {
        parent::__construct();

        $this->columnName = $columnName;
        $this->collateName = $collateName;
        $this->order = $order;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->columnName = $this->columnName;
        $ret->collateName = $this->collateName;
        $ret->order = $this->order;
    }


    /**
     * @inheritDoc
     */
    protected static function onParse(SqliteTokenStream $tokens) : ?static
    {
        $columnName = $tokens->expectName();

        $collateName = null;
        if ($tokens->ifOptionalKeyword('COLLATE')) {
            $collateName = $tokens->expectName();
        }

        $order = $tokens->optionalAnyKeyword('ASC', 'DESC');

        return new static($columnName, $collateName, $order);
    }
}