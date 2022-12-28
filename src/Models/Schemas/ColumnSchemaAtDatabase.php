<?php

namespace Magpie\Models\Schemas;

use Magpie\General\Concepts\Packable;
use Magpie\General\Packs\PackContext;
use Magpie\General\Traits\CommonPackable;

/**
 * Column schema at database level
 */
abstract class ColumnSchemaAtDatabase implements Packable
{
    use CommonPackable;


    /**
     * Column name
     * @return string
     */
    public abstract function getName() : string;


    /**
     * Column definition type
     * @return string
     */
    public abstract function getDefinitionType() : string;


    /**
     * If current column is non-null
     * @return bool
     */
    public abstract function isNonNull() : bool;


    /**
     * If current column is (or is part of) the primary key
     * @return bool
     */
    public abstract function isPrimaryKey() : bool;


    /**
     * If current column is unique
     * @return bool
     */
    public abstract function isUnique() : bool;


    /**
     * If current column is auto increment
     * @return bool
     */
    public abstract function isAutoIncrement() : bool;


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        $ret->name = $this->getName();
        $ret->definitionType = $this->getDefinitionType();
        $ret->isNonNull = $this->isNonNull();
    }
}