<?php

namespace Magpie\Models\Schemas\DatabaseEdits;

/**
 * Add a column when editing table on database
 */
class AddColumnDatabaseEditAction extends ColumnDatabaseEditAction
{
    /**
     * Create an instance
     * @return static
     */
    public static function create() : static
    {
        return new static();
    }
}