<?php

namespace Magpie\Models\Schemas\DatabaseEdits;

use Magpie\Models\Concepts\ColumnDatabaseSpecifiable;
use Magpie\Models\Schemas\ColumnSchema;

/**
 * Table creator at database
 */
abstract class TableCreator extends CommonTableEditable
{
    /**
     * Add a column from given column schema
     * @param ColumnSchema $column
     * @return ColumnDatabaseSpecifiable
     */
    public function addColumnFromSchema(ColumnSchema $column) : ColumnDatabaseSpecifiable
    {
        $ret = $this->addColumn($column->getName());

        static::mergeColumnFromSchema($ret, $column);

        return $ret;
    }
}