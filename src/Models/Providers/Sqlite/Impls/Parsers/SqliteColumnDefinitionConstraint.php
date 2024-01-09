<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

use Magpie\General\Concepts\TypeClassable;
use Magpie\General\Packs\PackContext;

/**
 * Column definition constraint for SQLite
 * @internal
 */
abstract class SqliteColumnDefinitionConstraint extends SqliteConstraint implements TypeClassable
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
    protected static final function getSpecificClasses() : iterable
    {
        yield SqlitePrimaryKeyColumnDefinitionConstraint::class;
        yield SqliteNotNullColumnDefinitionConstraint::class;
        yield SqliteUniqueColumnDefinitionConstraint::class;
        yield SqliteCheckColumnDefinitionConstraint::class;
        yield SqliteDefaultColumnDefinitionConstraint::class;
        yield SqliteCollateColumnDefinitionConstraint::class;
    }
}