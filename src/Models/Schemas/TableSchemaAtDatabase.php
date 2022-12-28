<?php

namespace Magpie\Models\Schemas;

use Magpie\General\Concepts\Packable;
use Magpie\General\Packs\PackContext;
use Magpie\General\Traits\CommonPackable;

/**
 * Table schema at database level
 */
abstract class TableSchemaAtDatabase implements Packable
{
    use CommonPackable;


    /**
     * Table name
     * @return string
     */
    public abstract function getName() : string;


    /**
     * Columns of the table
     * @return iterable<ColumnSchemaAtDatabase>
     */
    public abstract function getColumns() : iterable;


    /**
     * Specific column of the table
     * @param string $name
     * @return ColumnSchemaAtDatabase|null
     */
    public abstract function getColumn(string $name) : ?ColumnSchemaAtDatabase;


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        $ret->name = $this->getName();
        $ret->columns = $this->getColumns();
    }
}