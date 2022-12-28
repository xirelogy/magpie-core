<?php

namespace Magpie\Models\Schemas\DatabaseEdits;

/**
 * Change an existing column when editing table on database
 */
class ChangeColumnDatabaseEditAction extends ColumnDatabaseEditAction
{
    /**
     * @var string Name of existing column to be changed
     */
    public readonly string $columnName;


    /**
     * Constructor
     * @param string $columnName
     */
    protected function __construct(string $columnName)
    {
        parent::__construct();

        $this->columnName = $columnName;
    }


    /**
     * Create an instance
     * @param string $columnName
     * @return static
     */
    public static function create(string $columnName) : static
    {
        return new static($columnName);
    }
}