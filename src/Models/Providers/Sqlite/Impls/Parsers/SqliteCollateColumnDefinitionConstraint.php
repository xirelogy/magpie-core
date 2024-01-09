<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

use Magpie\General\Packs\PackContext;

/**
 * Column definition constraint for SQLite: 'COLLATE'
 * @internal
 */
class SqliteCollateColumnDefinitionConstraint extends SqliteColumnDefinitionConstraint
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'COLLATE';

    /**
     * @var string Collation name
     */
    public readonly string $collateName;


    /**
     * Constructor
     * @param string|null $name
     * @param string $collateName
     */
    protected function __construct(?string $name, string $collateName)
    {
        parent::__construct($name);

        $this->collateName = $collateName;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->collateName = $this->collateName;
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
        if (!$tokens->ifOptionalKeyword('COLLATE')) return null;

        $collateName = $tokens->expectName();

        return new static($name, $collateName);
    }
}